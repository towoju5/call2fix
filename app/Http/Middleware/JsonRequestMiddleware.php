<?php

namespace App\Http\Middleware;

use Closure;
use Http;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            if($user->is_guest == true && $request->isMethod('post')) {
                return get_error_response('Action not allowed', ['error' => 'Action not allowed'], 403);
            }

            if ($user->roles->isEmpty()) {
                $user->assignRole($user->account_type);
            }

        }

        // Http::get(url('generate-ref-accounts'));
        
        $request->headers->add(['Accept' => 'application/json']);
        return $next($request);
    }
}
