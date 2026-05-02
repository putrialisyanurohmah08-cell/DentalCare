<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\TwoFactorLoginService;
use App\Support\AuthRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, TwoFactorLoginService $twoFactor): RedirectResponse
    {
        $user = $request->authenticate();
        $redirect = AuthRedirect::pathFromRequestOrDefault($request, $user);
        $remember = $request->boolean('remember');

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->regenerate();

            $twoFactor->start($user, $request, $remember, $redirect);

            return redirect()->route('two-factor.login')
                ->with('status', 'Masukkan kode dari aplikasi autentikator Anda.');
        }

        Auth::guard('web')->login($user, $remember);

        $request->session()->regenerate();

        return redirect()->intended($redirect ?: AuthRedirect::pathFor($user));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
