<?php

namespace App\Http\Middleware;

use App\Support\BmsNavigation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBmsAccess
{
    public function handle(Request $request, Closure $next, ?string $pageId = null): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('bms.login');
        }

        if ($pageId && ! BmsNavigation::canAccessPage($user, $pageId)) {
            abort(403, 'You do not have access to this page.');
        }

        return $next($request);
    }
}
