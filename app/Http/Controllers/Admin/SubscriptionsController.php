<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Creatydev\Plans\Models\PlanSubscriptionModel as PlanSubscription;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{
    // Display a listing of subscriptions
    public function index()
    {
        $subscriptions = PlanSubscription::with('plan')->get();
        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    // Show the form for creating a new subscription
    public function create()
    {
        $plans = Plan::all();
        return view('admin.subscriptions.create', compact('plans'));
    }

    // Store a newly created subscription in storage
    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'model_id' => 'required|integer',
            'model_type' => 'required|string',
            'is_paid' => 'boolean',
            'charging_price' => 'nullable|numeric',
            'charging_currency' => 'nullable|string|max:3',
            'starts_on' => 'nullable|date',
            'expires_on' => 'nullable|date',
            'is_recurring' => 'boolean',
            'recurring_each_days' => 'integer'
        ]);

        PlanSubscription::create($request->all());

        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription created successfully.');
    }

    // Show the form for editing the specified subscription
    public function edit(PlanSubscription $subscription)
    {
        $plans = Plan::all();
        return view('admin.subscriptions.edit', compact('subscription', 'plans'));
    }

    // Update the specified subscription in storage
    public function update(Request $request, PlanSubscription $subscription)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'is_paid' => 'boolean',
            'charging_price' => 'nullable|numeric',
            'charging_currency' => 'nullable|string|max:3',
            'starts_on' => 'nullable|date',
            'expires_on' => 'nullable|date',
            'is_recurring' => 'boolean',
            'recurring_each_days' => 'integer'
        ]);

        $subscription->update($request->all());

        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription updated successfully.');
    }

    // Remove the specified subscription from storage
    public function destroy(PlanSubscription $subscription)
    {
        $subscription->delete();
        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription deleted successfully.');
    }
}
