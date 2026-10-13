<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Approval gate: tenant-role users whose account is still 'pending'
 * (not yet linked to a Tenant record by an admin) may only see the
 * waiting-approval page. Suspended accounts are logged out.
 */
class EnsureApproval
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($user->status === 'suspended') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been suspended. Please contact the administrator.',
            ]);
        }

        // Allow-list: only 'approved' tenants proceed. Anything else
        // (pending, null, unknown) is held at the waiting page.
        if ($user->status !== 'approved' && ! $request->routeIs('waiting.approval', 'logout')) {
            return redirect()->route('waiting.approval');
        }

        return $next($request);
    }
}
