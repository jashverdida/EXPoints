<?php

namespace App\Http\Middleware;

use App\Models\UserInfo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    /**
     * Handle an incoming request.
     *
     * Check if the authenticated user is banned.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Check if account is disabled
        if ($user->isDisabled()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account has been disabled.',
                    'reason' => $user->disabled_reason ?? 'No reason provided.'
                ], 403);
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been disabled. Reason: ' . ($user->disabled_reason ?? 'No reason provided.')]);
        }

        // Check if user is banned (via user_info)
        $userInfo = UserInfo::findByUserId($user->id);

        if ($userInfo && $userInfo->isBanned()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account has been banned.',
                    'reason' => $userInfo->ban_reason ?? 'No reason provided.'
                ], 403);
            }

            return redirect()->route('banned')
                ->with('ban_reason', $userInfo->ban_reason ?? 'No reason provided.');
        }

        return $next($request);
    }
}
