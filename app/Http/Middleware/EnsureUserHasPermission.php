<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Handle an incoming request.
     *
     * Usage: route ->middleware('permission:dashboard.view,support.view')
     * Administrators always pass.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasRole('Administrator')) {
            return $next($request);
        }

        if (! $user->hasAnyPermission($permissions)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
