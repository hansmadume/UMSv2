<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InactivityTimeout
{
    /**
     * Handle an incoming request.
     *
     * If the user has been inactive for longer than AUTH_INACTIVITY_TIMEOUT_SECONDS,
     * log them out and redirect to login with a notice.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $timeout = (int) env('AUTH_INACTIVITY_TIMEOUT_SECONDS', 1800);
        $now = time();
        $last = $request->session()->get('last_activity_time');

        if ($last && ($now - (int) $last) > $timeout) {
            $user = Auth::user();
            AuditLog::create([
                'user_id' => $user?->id,
                'user_name' => $user?->getDisplayName(),
                'action' => 'Session Expired (Inactivity)',
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('login_notice', 'You were logged out due to inactivity. Please log in again.');
        }

        // Stamp activity for the next request.
        $request->session()->put('last_activity_time', $now);

        return $next($request);
    }
}
