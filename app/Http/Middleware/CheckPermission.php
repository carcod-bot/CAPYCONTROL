<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para realizar esta acción.'], 403);
            }
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        return $next($request);
    }
}
