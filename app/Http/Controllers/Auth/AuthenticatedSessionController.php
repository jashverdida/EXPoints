<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\UserInfo;
use App\Providers\RouteServiceProvider;
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
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Check if user is disabled
        if ($user->isDisabled()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been disabled. Reason: ' . ($user->disabled_reason ?? 'No reason provided.')]);
        }

        // Check if user is banned
        $userInfo = UserInfo::findByUserId($user->id);
        if ($userInfo && $userInfo->isBanned()) {
            return redirect()->route('banned')
                ->with('ban_reason', $userInfo->ban_reason ?? 'No reason provided.');
        }

        // Role-based redirection
        return match ($user->role) {
            'admin' => redirect()->intended('/admin/dashboard'),
            'mod' => redirect()->intended('/mod/dashboard'),
            default => redirect()->intended(RouteServiceProvider::HOME),
        };
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
