<?php

namespace App\Http\Middleware;

use App\Support\Impersonation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->force_password_change && ! Impersonation::isActive()) {
            $changeRoute = route('bms.password.change');

            // Allow the change-password page and logout through
            if (! $request->routeIs('bms.password.change', 'bms.password.change.update', 'bms.logout', 'bms.impersonation.leave')) {
                return redirect($changeRoute);
            }
        }

        return $next($request);
    }
}
