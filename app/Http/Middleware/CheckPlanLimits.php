<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CheckPlanLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $merchant = auth()->user()->merchant;
        $plan = $merchant->plan;

        if (!Schema::hasColumn('users', 'parent_account')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_account')->nullable()->after('id');
            });
        }


        if ($plan) {
            // Check service category limit
            if ($plan->service_category_limit && $merchant->service_categories >= $plan->service_category_limit) {
                return response()->json(['message' => 'Service category limit reached'], 403);
            }

            // Check artisan limit
            if ($plan->artisan_limit && $merchant->artisans >= $plan->artisan_limit) {
                return response()->json(['message' => 'Artisan limit reached'], 403);
            }

            // Check product category limit
            if ($plan->product_category_limit && $merchant->product_categories >= $plan->product_category_limit) {
                return response()->json(['message' => 'Product category limit reached'], 403);
            }

            // Check product limit
            if ($plan->product_limit && $merchant->products >= $plan->product_limit) {
                return response()->json(['message' => 'Product limit reached'], 403);
            }
        }

        return $next($request);
    }
}
