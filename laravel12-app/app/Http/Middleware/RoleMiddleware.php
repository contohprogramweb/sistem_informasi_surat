<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk memastikan user memiliki role tertentu.
 * 
 * Usage: Route::middleware(['role:pimpinan,kabag'])->group(...)
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Cek apakah user memiliki salah satu role yang ditentukan
        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        // Jika tidak memiliki role yang sesuai, abort dengan 403
        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
