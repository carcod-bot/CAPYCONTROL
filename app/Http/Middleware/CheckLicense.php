<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SysGuard;

class CheckLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        // Prevent redirect loops on license, login, and logout routes
        if ($request->is('license*') || $request->is('api/license*') || $request->is('login*') || $request->is('logout*')) {
            return $next($request);
        }

        if (!SysGuard::verifyState()) {
            return redirect()->route('license.activate');
        }

        return $next($request);
    }
}
