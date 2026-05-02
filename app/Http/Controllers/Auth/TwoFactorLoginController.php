<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\TwoFactorLoginService;
use App\Support\AuthRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorLoginController extends Controller
{
    public function show(Request $request, TwoFactorLoginService $twoFactor): View|RedirectResponse
    {
        $challenge = $twoFactor->pendingChallenge($request);

        if (! $challenge) {
            return redirect()->route('login')->with('error', 'Sesi verifikasi 2FA sudah kedaluwarsa. Silakan login ulang.');
        }

        return view('auth.two-factor-challenge', [
            'expiresAt' => $challenge->expires_at,
            'digits' => $this->digits(),
        ]);
    }

    public function store(Request $request, TwoFactorLoginService $twoFactor): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:20'],
        ]);

        [$user, $remember, $redirectPath] = $twoFactor->verify($request, $validated['code']);

        Auth::guard('web')->login($user, $remember);

        $request->session()->regenerate();

        return redirect()->intended($redirectPath ?: AuthRedirect::pathFor($user));
    }

    public function destroy(Request $request, TwoFactorLoginService $twoFactor): RedirectResponse
    {
        $twoFactor->cancel($request);

        return redirect()->route('login')->with('status', 'Verifikasi 2FA dibatalkan. Silakan login ulang jika ingin melanjutkan.');
    }

    protected function digits(): int
    {
        return max(6, min(8, (int) config('two_factor.authenticator.digits', 6)));
    }
}
