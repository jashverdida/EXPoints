<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInfo;
use App\Services\SupabaseService;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:50', 'alpha_dash'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'first_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['nullable', 'string', 'max:50'],
        ]);

        // Check if email already exists
        $existingUser = User::findByEmail($request->email);
        if ($existingUser) {
            return back()->withErrors(['email' => 'The email has already been taken.'])->withInput();
        }

        // Check if username already exists
        $existingUsername = UserInfo::findByUsername($request->username);
        if ($existingUsername) {
            return back()->withErrors(['username' => 'The username has already been taken.'])->withInput();
        }

        try {
            // Create user in Supabase
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user',
                'is_disabled' => 0
            ]);

            // Create user_info record
            UserInfo::create([
                'user_id' => $user->id,
                'username' => $request->username,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'exp_points' => 0,
                'is_banned' => 0
            ]);

            event(new Registered($user));

            Auth::login($user);

            return redirect(RouteServiceProvider::HOME);

        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Registration failed: ' . $e->getMessage()])->withInput();
        }
    }
}
