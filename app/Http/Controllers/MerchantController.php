<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function subscribe(Request $request)
    {
        $plan = Plan::findOrFail($request->input('plan_id'));
        $merchant = auth()->user()->merchant;

        $merchant->plan()->associate($plan);
        $merchant->save();

        return get_success_response(['message' => 'Subscription successful', 'plan' => $plan->name]);
    }

    public function getMerchantDetails()
    {
        $merchant = auth()->user()->merchant;

        return get_success_response([
            'plan' => $merchant->plan ? $merchant->plan->name : 'No Plan',
            'service_categories' => $merchant->service_categories,
            'artisans' => $merchant->artisans,
            'product_categories' => $merchant->product_categories,
            'products' => $merchant->products,
        ]);
    }
}
