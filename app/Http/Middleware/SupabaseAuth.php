<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SupabaseAuth
{
    /**
     * Handle an incoming request.
     * Checks if user is authenticated via session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!session('authenticated') || session('authenticated') !== true) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            return redirect()->route('login')->with('error', 'Please login to continue');
        }

        // Check if session has required user data
        if (!session('user_id') || !session('user_email')) {
            session()->flush();

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid session'], 401);
            }

            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        return $next($request);
    }
}
