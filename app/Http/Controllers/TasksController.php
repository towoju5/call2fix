<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tasks as AffiliateTask;
use App\Models\Subscription;
use App\Models\Earning;
use App\Models\Referral;
use Illuminate\Support\Str;

class TasksController extends Controller
{
    // Fetch all tasks
    public function index()
    {
        $tasks = AffiliateTask::with('earnings')->get();
        return get_success_response($tasks, "Affiliate tasks retrieved successfully");
    }

    public function monthlyTasks()
    {
        $today = now(); // Get today's date
        $tasks = AffiliateTask::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->with('earnings')
            ->get();

        return get_success_response($tasks, "Affiliate tasks retrieved successfully");
    }
    
    // Subscribe to a task and generate a unique referral code
    public function subscribeToTask(Request $request, $taskId)
    {
        $user = auth()->user();
        $task = AffiliateTask::findOrFail($taskId);

        // Check if the user is already subscribed
        if ($user->task_subscriptions()->where('task_id', $taskId)->exists()) {
            // return get_error_response("You are already subscribed to this task.");
            $task_subscriptions = Subscription::where([
                'user_id' => $user->id,
                'task_id' => $taskId,
            ])->first();
            return get_success_response([
                'task' => $task,
                'referral_code' => $task_subscriptions->referral_code,
            ], "Subscribed to task successfully.");
        }

        // Generate a unique referral code
        $referralCode = strtoupper(Str::random(8));

        // Create subscription
        Subscription::create([
            'user_id' => $user->id,
            'task_id' => $taskId,
            'referral_code' => $referralCode,
        ]);

        return get_success_response([
            'task' => $task,
            'referral_code' => $referralCode,
        ], "Subscribed to task successfully.");
    }

    // Handle referrals and assign commission
    public function handleReferral(Request $request, $referralCode)
    {
        $subscription = Subscription::where('referral_code', $referralCode)->first();

        if (!$subscription) {
            return get_error_response("Invalid referral code.");
        }

        $referrer = $subscription->user;
        $task = $subscription->task;

        // Check if the referral limit is reached
        $referralCount = Referral::where('referrer_id', $referrer->id)
            ->where('task_id', $task->id)
            ->count();

        if ($referralCount >= $task->ref_required_to_complete) {
            return get_error_response("Referral limit for this task has been reached.");
        }

        // Create referral record
        Referral::create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => auth()->id(),
            'task_id' => $task->id,
        ]);

        // Assign commission
        $commission = $task->pay_per_invite;
        Earning::create([
            'user_id' => $referrer->id,
            'task_id' => $task->id,
            'amount' => $commission,
        ]);

        $referrer->increment('balance', $commission);

        return get_success_response([
            'commission' => $commission,
        ], "Referral successful, and commission added.");
    }

    // Fetch all referrals
    public function allReferal()
    {
        $referrals = Referral::byReferrer(auth()->id())->with([ 'referrer:id,first_name,last_name,profile_picture','referredUser:id,first_name,last_name,profile_picture'])->get();
        return get_success_response($referrals, "All referrals retrieved successfully.");
    }


    // Fetch referrals by task
    public function taskReferrals($taskId)
    {
        $referrals = Referral::byTask($taskId)
            ->byReferrer(auth()->id())
            ->get();

        return get_success_response($referrals, "Referrals for the task retrieved successfully.");
    }

    // Fetch single referral by userId and referrerId
    public function userReferralByTask($userId, $referrerId)
    {
        $referral = Referral::where('referred_user_id', $userId)
            ->where('referrer_id', $referrerId)
            ->first();


        return get_success_response($referral, "Referrals for the task retrieved successfully.");
    }

    // Get current month's referrals
    public function monthlyReferrals()
    {
        $referrals = Referral::where('referrer_id', auth()->id())->with([ 'referrer:id,first_name,last_name,profile_picture','referredUser:id,first_name,last_name,profile_picture'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get();

        return get_success_response($referrals, "Current month's referrals retrieved successfully.");
    }

    // Get earnings for the current month
    public function currentMonthEarnings()
    {
        $earnings = Earning::where('user_id', auth()->id())
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return get_success_response(['total_earnings' => $earnings], "Earnings for the current month retrieved successfully.");
    }

    // Get all-time referral earnings
    public function allTimeEarnings()
    {
        $earnings = Earning::where('user_id', auth()->id())->with('task')->get();
        $total_earnings = $earnings->sum('amount');

        return get_success_response(['total_earnings' => $total_earnings, "earnings" => $earnings], "All-time earnings retrieved successfully.");
    }
    
    public function earningsBreakdown()
    {
        //
    }

    // Claim task earnings
    public function claimTaskEarnings(Request $request, $taskId)
    {
        $user = auth()->user();

        // Check if user is subscribed to the task
        $subscription = Subscription::where('user_id', $user->id)
            ->where('task_id', $taskId)->with(['task'])
            ->first();

        if (!$subscription) {
            return get_error_response("You are not subscribed to this task.");
        }

        // Fetch completed referrals for the task
        $referralsCount = Referral::where('referrer_id', $user->id)
            ->where('task_id', $taskId)
            ->count();

        $task = $subscription->task;
        
        if(!$task) {
            return get_error_response("Task not found", ["error" => "Task not found"]);
        }

        if ($referralsCount < $task->ref_required_to_complete) {
            return get_error_response("You have not completed the required referrals for this task.", ["error" => "You have not completed the required referrals for this task."]);
        }

        // Calculate total earnings for the task
        $earnings = Earning::where('user_id', $user->id)
            ->where('task_id', $taskId)
            ->sum('amount');

        if ($earnings <= 0) {
            return get_error_response("No earnings available to claim for this task.", ['error' => "No earnings available to claim for this task."]);
        }

        // Process claiming earnings
        DB::transaction(function () use ($user, $earnings, $taskId) {
            // $user->increment('balance', $earnings);
            // credit customer's balance

            // Optionally, mark task as claimed (if such a column exists)
            Earning::where('user_id', $user->id)
                ->where('task_id', $taskId)
                ->update(['claimed' => true]);
        });

        return get_success_response([
            'task_id' => $taskId,
            'earnings_claimed' => $earnings,
        ], "Earnings for the task claimed successfully.");
    }
}
