<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\Auth\AuthenticatorAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private const TWO_FACTOR_SETUP_SECRET = 'auth.two_factor_setup_secret';

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function twoFactorSetup(Request $request, AuthenticatorAppService $authenticator): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return Redirect::route('profile.edit');
        }

        $secret = $request->session()->get(self::TWO_FACTOR_SETUP_SECRET);

        if (blank($secret)) {
            $secret = $authenticator->generateSecret();
            $request->session()->put(self::TWO_FACTOR_SETUP_SECRET, $secret);
        }

        $otpauthUrl = $authenticator->otpauthUrl($user, $secret);

        return view('profile.two-factor-setup', [
            'secret' => $secret,
            'qrCodeSvg' => $authenticator->qrCodeSvg($otpauthUrl),
            'digits' => max(6, min(8, (int) config('two_factor.authenticator.digits', 6))),
        ]);
    }

    public function enableTwoFactor(Request $request, AuthenticatorAppService $authenticator): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:8'],
        ]);

        $secret = $request->session()->get(self::TWO_FACTOR_SETUP_SECRET);

        if (blank($secret)) {
            return Redirect::route('profile.two-factor.setup')
                ->with('error', 'Sesi aktivasi 2FA sudah kedaluwarsa. Silakan mulai lagi.');
        }

        if (! $authenticator->verifyCodeForSecret($secret, $validated['code'])) {
            return back()->withErrors(['code' => 'Kode 2FA tidak valid.'])->withInput();
        }

        $recoveryCodes = $authenticator->generateRecoveryCodes();

        $request->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget(self::TWO_FACTOR_SETUP_SECRET);

        return Redirect::route('profile.edit')
            ->with('status', '2FA berhasil diaktifkan. Simpan recovery code Anda di tempat aman.')
            ->with('recovery_codes', $recoveryCodes);
    }

    public function regenerateRecoveryCodes(Request $request, AuthenticatorAppService $authenticator): RedirectResponse
    {
        $request->validateWithBag('recoveryCodes', [
            'password' => ['required', 'current_password'],
        ]);

        if (! $request->user()->hasTwoFactorEnabled()) {
            return Redirect::route('profile.edit')->with('error', '2FA belum aktif.');
        }

        $recoveryCodes = $authenticator->generateRecoveryCodes();

        $request->user()->forceFill([
            'two_factor_recovery_codes' => $recoveryCodes,
        ])->save();

        return Redirect::route('profile.edit')
            ->with('status', 'Recovery code baru berhasil dibuat.')
            ->with('recovery_codes', $recoveryCodes);
    }

    public function disableTwoFactor(Request $request): RedirectResponse
    {
        $request->validateWithBag('disableTwoFactor', [
            'password' => ['required', 'current_password'],
        ]);

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $request->session()->forget(self::TWO_FACTOR_SETUP_SECRET);

        return Redirect::route('profile.edit')->with('status', '2FA berhasil dinonaktifkan.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
