<?php

namespace App\Services\Auth;

use App\Models\TwoFactorChallenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TwoFactorLoginService
{
    public const SESSION_KEY = 'auth.two_factor.challenge_id';

    public function __construct(private readonly AuthenticatorAppService $authenticator)
    {
    }

    public function start(User $user, Request $request, bool $remember, ?string $redirectPath): TwoFactorChallenge
    {
        $this->invalidatePendingChallenges($user);

        $challenge = TwoFactorChallenge::create([
            'public_id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'code_hash' => Hash::make(Str::random(40)),
            'remember' => $remember,
            'redirect_path' => $redirectPath,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'last_sent_at' => now(),
            'expires_at' => now()->addMinutes($this->expiresMinutes()),
        ]);

        $request->session()->put(self::SESSION_KEY, $challenge->public_id);

        return $challenge;
    }

    public function pendingChallenge(Request $request): ?TwoFactorChallenge
    {
        $publicId = $request->session()->get(self::SESSION_KEY);

        if (blank($publicId)) {
            return null;
        }

        $challenge = TwoFactorChallenge::with('user')
            ->where('public_id', $publicId)
            ->first();

        if (! $challenge || ! $challenge->isActive() || ! $challenge->user->hasTwoFactorEnabled()) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        return $challenge;
    }

    /**
     * @return array{0: User, 1: bool, 2: string|null}
     */
    public function verify(Request $request, string $code): array
    {
        $publicId = $request->session()->get(self::SESSION_KEY);

        if (blank($publicId)) {
            throw ValidationException::withMessages([
                'code' => 'Sesi verifikasi sudah kedaluwarsa. Silakan login ulang.',
            ]);
        }

        return DB::transaction(function () use ($publicId, $request, $code): array {
            $challenge = TwoFactorChallenge::with('user')
                ->where('public_id', $publicId)
                ->lockForUpdate()
                ->first();

            if (! $challenge || $challenge->consumed_at !== null || ! $challenge->user->hasTwoFactorEnabled()) {
                $request->session()->forget(self::SESSION_KEY);

                throw ValidationException::withMessages([
                    'code' => 'Sesi verifikasi sudah kedaluwarsa. Silakan login ulang.',
                ]);
            }

            if ($challenge->expires_at->isPast()) {
                $challenge->forceFill(['consumed_at' => now()])->save();
                $request->session()->forget(self::SESSION_KEY);

                throw ValidationException::withMessages([
                    'code' => 'Sesi verifikasi sudah kedaluwarsa. Silakan login ulang.',
                ]);
            }

            if ($challenge->attempts >= $this->maxAttempts()) {
                $challenge->forceFill(['consumed_at' => now()])->save();
                $request->session()->forget(self::SESSION_KEY);

                throw ValidationException::withMessages([
                    'code' => 'Terlalu banyak percobaan 2FA. Silakan login ulang.',
                ]);
            }

            $challenge->forceFill(['attempts' => $challenge->attempts + 1])->save();

            $validCode = $this->authenticator->verifyCode($challenge->user, $code);
            $validRecoveryCode = $validCode ? false : $this->authenticator->consumeRecoveryCode($challenge->user, $code);

            if (! $validCode && ! $validRecoveryCode) {
                if ($challenge->attempts >= $this->maxAttempts()) {
                    $challenge->forceFill(['consumed_at' => now()])->save();
                    $request->session()->forget(self::SESSION_KEY);

                    throw ValidationException::withMessages([
                        'code' => 'Terlalu banyak percobaan 2FA. Silakan login ulang.',
                    ]);
                }

                throw ValidationException::withMessages([
                    'code' => 'Kode 2FA tidak valid.',
                ]);
            }

            $challenge->forceFill(['consumed_at' => now()])->save();
            $request->session()->forget(self::SESSION_KEY);

            return [$challenge->user, $challenge->remember, $challenge->redirect_path];
        });
    }

    public function cancel(Request $request): void
    {
        $challenge = $this->pendingChallenge($request);

        if ($challenge) {
            $challenge->forceFill(['consumed_at' => now()])->save();
        }

        $request->session()->forget(self::SESSION_KEY);
    }

    protected function invalidatePendingChallenges(User $user): void
    {
        TwoFactorChallenge::query()
            ->where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);
    }

    protected function expiresMinutes(): int
    {
        return max(1, (int) config('two_factor.authenticator.challenge_expires_minutes', 10));
    }

    protected function maxAttempts(): int
    {
        return max(1, (int) config('two_factor.authenticator.max_attempts', 5));
    }
}
