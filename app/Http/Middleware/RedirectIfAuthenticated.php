<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // Use session-based auth check (like original PHP)
        // This avoids Supabase API calls on every page load
        if (session('authenticated') === true) {
            $role = session('user_role', 'user');

            return match($role) {
                'admin' => redirect()->route('admin.dashboard'),
                'mod' => redirect()->route('mod.dashboard'),
                default => redirect()->route('dashboard'),
            };
        }

        return $next($request);
    }
}
