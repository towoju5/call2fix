<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Jijunair\LaravelReferral\Models\Referral;
use Modules\Artisan\Models\Task as AffiliateTask;
use App\Models\User as Affiliate;
use App\Models\ServiceProvider;
use Illuminate\Support\Facades\DB;

class TasksController extends Controller
{
    public function index()
    {
        $tasks = AffiliateTask::all();
        return get_success_response($tasks, "Affiliate tasks rertrieved successfully");
    }

    public function monthlyTasks()
    {
        $tasks = AffiliateTask::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->get();
        return get_success_response($tasks, "Affiliate tasks rertrieved successfully");
    }

    public function allReferal()
    {
        $tasks = Referral::where('referrer_id', auth()->user()->id)->get();
        return get_success_response($tasks, "Record retrieved successfully");
    }

    public function monthlyReferrals()
    {
        $tasks = Referral::where('referrer_id', auth()->user()->id)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->get();
        return get_success_response($tasks, "Record retrieved successfully");
    }

    public function createAffiliateTask(Request $request)
    {
        $validatedData = $request->validate([
            'affiliate_id' => 'required|exists:affiliates,id',
            'task_type' => 'required|in:refer_sp,monthly_target',
            'target_count' => 'required|integer|min:1',
            'due_date' => 'required|date',
        ]);

        $affiliateTask = AffiliateTask::create($validatedData);

        return get_success_response($affiliateTask, "Affiliate task created successfully");
    }

    public function getAffiliateTasks(Affiliate $affiliate)
    {
        $tasks = AffiliateTask::where('affiliate_id', $affiliate->id)->get();
        return get_success_response($tasks);
    }

    public function updateAffiliateTask(Request $request, AffiliateTask $task)
    {
        $validatedData = $request->validate([
            'task_type' => 'sometimes|in:refer_sp,monthly_target',
            'target_count' => 'sometimes|integer|min:1',
            'due_date' => 'sometimes|date',
            'completed' => 'sometimes|boolean',
        ]);

        $task->update($validatedData);

        return get_success_response($task, "Affiliate task updated successfully");
    }

    public function checkAffiliateStatus(Affiliate $affiliate)
    {
        $referredSPs = User::role(Controller::SERVICE_PROVIDERS)->where('referred_by', $affiliate->id)
                        ->where('onboarding_completed', true)
                        ->count();

        if ($referredSPs >= 5) {
            $affiliate->activate();
            return get_success_response(['is_active' => true], "Affiliate activated successfully");
        }

        return get_success_response(['is_active' => false], "Affiliate not yet eligible for activation");
    }

    public function calculateAffiliateCommission(User $serviceProvider, $jobIncome)
    {
        $affiliate = $serviceProvider->referrer;
        if ($affiliate && $affiliate->is_active) {
            $commission = $jobIncome * 0.5; // 50% commission
            DB::transaction(function () use ($affiliate, $commission) {
                $affiliate->addCommission($commission);
            });
            return get_success_response(['commission' => $commission], "Commission calculated and added successfully");
        }
        return get_error_response("No active affiliate found for this service provider");
    }

    public function updateAffiliateActivity(Affiliate $affiliate)
    {
        $lastMonthReferrals = User::rola(Controller::SERVICE_PROVIDERS)->where('referred_by', $affiliate->id)
                                ->where('created_at', '>=', now()->subDays(30))
                                ->count();

        if ($lastMonthReferrals < 2) {
            $affiliate->deactivate();
            return get_success_response(['is_active' => false], "Affiliate deactivated due to insufficient monthly referrals");
        }
        return get_success_response(['is_active' => true], "Affiliate remains active");
    }

    public function processAffiliateWithdrawal(Request $request, Affiliate $affiliate)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        if ($affiliate->is_active && $affiliate->balance >= $validatedData['amount']) {
            DB::transaction(function () use ($affiliate, $validatedData) {
                $affiliate->withdraw($validatedData['amount']);
            });
            return get_success_response(['withdrawn_amount' => $validatedData['amount']], "Withdrawal processed successfully");
        }
        return get_error_response("Withdrawal failed. Check account status and balance.");
    }
}
