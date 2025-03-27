<?php

namespace App\Http\Controllers;

use App\Models\User;
use Creatydev\Plans\Models\PlanModel;
use Illuminate\Http\Request;
use Laravelcm\Subscriptions\Models\Plan;

class PlanController extends Controller
{
    public function index()
    {
        try {
            $plans = PlanModel::all();
            if (!$plans) {
                return get_error_response("No Plan Found!", ['error' => "Plans not found"], 404);
            }

            return get_success_response(['plans' => $plans, 'current_plan' => auth()->user()->activeSubscription()]);
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
            
            return get_success_response($plan->features);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()], 400);
        }
    }

    public function subscribe($planId)
    {
        try {

            $user = User::find(auth()->id());
            $plan = Plan::find($planId);

            if(!$plan) {
                return get_error_response("No Plan Found!", ['error' => "Plans not found"], 404);
            }

            // if(count($user->subscribedPlans()) > 0) {
            //     return get_error_response('Please, Cancel your current plan to upgrade to a new plan', ['error' => 'Please, Cancel your current plan to upgrade to a new plan'], 402);
            // }

            // charge the customer
            $wallet = $user->getWallet('ngn');

            if (!$wallet->withdraw($plan->price * 100)) {
                return get_error_response('Insufficient wallet balance', ['error' => 'Insufficient wallet balance'], 402);
            }

            $currentSubscription = $user->activeSubscription();

            if ($currentSubscription) {
                $user->upgradeCurrentPlanTo($plan);
                return get_success_response(['message' => 'Subscription upgraded successfully']);
            }

            
            $duration = $request->duration ?? 30;
            $renewal = $request->auto_renew ?? true;
            $subscription = $user->subscribeTo($plan, $duration, $renewal);
            // if ($user->newPlanSubscription($plan->name, $plan)) {
            if ($subscription) {
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
            
            if (!$wallet->withdraw($plan->amount * 100)) {
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

    /**
     * Customer to upgrade or downgrade subscription plan
     * @param mixed $planId
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function cancelPlan($planId)
    {
        try {

            $user = User::find(auth()->id());
            $plan = PlanModel::find($planId);
            if ($plan) {
                $subscription = $user->planSubscription($plan->name);
                if($user->cancelCurrentSubscription()) {
                    return get_success_response([], "Subscription plan was canceled successful");
                }
            }

            return get_error_response($plan->getMessage(), ['error', "Unable to change subscription plan"], 400);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()], 400);
        }
    }
}
