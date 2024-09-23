<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravelcm\Subscriptions\Models\Plan;

class PlanController extends Controller
{
    public function index()
    {
        try {
            $plans = Plan::all();
            if (!$plans) {
                return get_error_response("No Plan Found!", ['error' => "Plans not found"], 404);
            }

            return get_success_response($plans);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()], 400);
        }
    }

    public function planFeatures($planId)
    {
        try {
            $plan = Plan::find($planId);
            if (!$plan) {
                return get_error_response("No Plan Found!", ['error' => "Plans not found"], 404);
            }
            $plan->features;
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()], 400);
        }
    }

    public function subscribe($planId)
    {
        try {

            $user = User::find(auth()->id());
            $plan = Plan::find($planId);

            // charge the customer
            $wallet = $user->getWallet('ngn');

            if (!$wallet->withdraw($plan->amount)) {
                return get_error_response('Insufficient wallet balance', ['error' => 'Insufficient wallet balance'], 402);
            }

            if ($user->newPlanSubscription($plan->name, $plan)) {
                return get_success_response([], "Plan subscription was successful");
            }
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()], 400);
        }
    }

    /**
     * Customer to upgrade or downgrade subscription plan
     * @param mixed $planId
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function changePlan($planId)
    {
        try {

            $user = User::find(auth()->id());
            $plan = Plan::find($planId);

            // charge the customer
            $wallet = $user->getWallet('ngn');
            
            if (!$wallet->withdraw($plan->amount)) {
                return get_error_response('Insufficient wallet balance', ['error' => 'Insufficient wallet balance'], 402);
            }

            if ($user->newPlanSubscription($plan->name, $plan)) {
                return get_success_response([], "Plan subscription was successful");
            }

            return get_error_response($plan->getMessage(), ['error', "Unable to change subscription plan"], 400);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()], 400);
        }
    }
}
