<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Route;
use Symfony\Component\HttpFoundation\Response;

class PlanMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $feature): Response
    {
        $user = $request->user();
        if (!$user || !$user->subscribed()) {
            return response()->json(['error' => 'Subscription required'], 403);
        }

        return response()->json(['result' => $feature]);

        if ($user->hasActiveSubscription()) {
            $subscription = $user->activeSubscription();
            if($subscription->getRemainingOf($feature) > 0) {
                $subscription->consumeFeature($feature, 5);
            } else {
                return get_error_response('Feature limit reached', ['error' => 'Feature limit reached'], 403);
            }
        } else {
            return get_error_response("No active Subscription", ['error' => "User has no active subscription plan"]);
        }

        return $next($request);
    }
}
