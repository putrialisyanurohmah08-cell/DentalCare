<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! $this->hasGoogleConfiguration()) {
            return redirect()->route('login')->with('error', 'Konfigurasi login Google belum lengkap.');
        }

        try {
            return Socialite::driver('google')->redirect();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->with('error', 'Gagal menghubungkan ke Google. Silakan coba lagi.');
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! $this->hasGoogleConfiguration()) {
            return redirect()->route('login')->with('error', 'Konfigurasi login Google belum lengkap.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->with('error', 'Login Google gagal. Silakan coba lagi.');
        }

        $googleId = $googleUser->getId();
        $email = Str::lower((string) $googleUser->getEmail());

        if (blank($googleId) || blank($email)) {
            return redirect()->route('login')->with('error', 'Akun Google harus membagikan email yang valid.');
        }

        $user = User::query()->firstWhere('google_id', $googleId);

        if (! $user) {
            $user = User::query()->firstWhere('email', $email);

            if ($user && filled($user->google_id) && $user->google_id !== $googleId) {
                return redirect()->route('login')->with('error', 'Email ini sudah terhubung ke akun Google lain.');
            }
        }

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'role' => UserRole::Patient,
                'password' => Hash::make(Str::random(32)),
                'google_id' => $googleId,
                'google_avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]);

            event(new Registered($user));
        } else {
            if (blank($user->google_id)) {
                $user->google_id = $googleId;
            }

            if (filled($googleUser->getAvatar())) {
                $user->google_avatar = $googleUser->getAvatar();
            }

            if (blank($user->name) && filled($googleUser->getName())) {
                $user->name = $googleUser->getName();
            }

            if (blank($user->email_verified_at)) {
                $user->email_verified_at = now();
            }

            if ($user->isDirty()) {
                $user->save();
            }
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    protected function hasGoogleConfiguration(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }
}
