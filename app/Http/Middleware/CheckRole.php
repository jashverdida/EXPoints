<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Allowed roles (e.g., 'admin', 'mod', 'user')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Use session-based authentication (Supabase)
        if (!session('authenticated') || session('authenticated') !== true) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Get role from session
        $userRole = session('user_role', 'user');

        // Admin has access to everything
        if ($userRole === 'admin') {
            return $next($request);
        }

        // Check if user's role is in the allowed roles
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // Access denied
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Access denied. Insufficient permissions.'], 403);
        }

        abort(403, 'Access denied. Insufficient permissions.');
    }
}
