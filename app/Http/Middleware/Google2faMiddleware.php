<?php

namespace App\Http\Middleware;

use Closure;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Auth;

class Google2faMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // Check if the user has enabled 2FA
        if ($user && $user->google2fa_secret) {
            $google2fa = new Google2FA();
            
            // Validate the OTP (One-Time Password)
            if (!$google2fa->verifyKey($user->google2fa_secret, $request->input('otp'))) {
                return get_error_response(['error' => 'Invalid OTP'], 401);
            }
        }

        return $next($request);
    }
}
