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
        // Prevent redirect loops on license routes
        if ($request->is('license*')) {
            return $next($request);
        }

        // If trying to logout, let them pass
        if ($request->is('logout') && $request->isMethod('post')) {
            return $next($request);
        }

        if (!SysGuard::verifyState()) {
            return redirect()->route('license.activate');
        }

        return $next($request);
    }
}
