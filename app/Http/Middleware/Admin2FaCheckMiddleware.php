<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Route;
use Symfony\Component\HttpFoundation\Response;

class Admin2FaCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth('admin')->check() && auth('admin')->user()->two_factor_code && Route::currentRouteName() !== 'admin.2fa.verify') {
            if (!session('admin_2fa_verified')) {
                return redirect()->route('admin.2fa.show');
            }
        } 
        return $next($request);
    }
}
