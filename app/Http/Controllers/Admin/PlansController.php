<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Http\Request;
use Str;

class PlansController extends Controller
{
    // Display a listing of plans
    public function index()
    {
        $plans = Plan::with('features')->get();
        return view('admin.plans.index', compact('plans'));
    }

    // Show the form for creating a new plan
    public function create()
    {
        return view('admin.plans.create');
    }

    // Store a newly created plan in storage
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'currency' => 'required|string|max:3',
            'duration' => 'required|integer'
        ]);

        $validate['slug'] = Str::slug($request->name).rand(1, 99);

        $plan = Plan::create($validate);

        // Optionally handle plan features if passed
        if ($request->has('features')) {
            foreach ($request->features as $feature) {
                $plan->features()->create($feature);
            }
        }

        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully.');
    }

    // Show the form for editing the specified plan
    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    // Update the specified plan in storage
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'currency' => 'required|string|max:3',
            'duration' => 'required|integer'
        ]);

        $plan->update($request->only(['name', 'description', 'price', 'currency', 'duration', 'metadata']));

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated successfully.');
    }

    // Remove the specified plan from storage
    public function destroy(Plan $plan)
    {
        if(str_contains(strtolower($plan->name), 'free')){
            return redirect()->route('admin.plans.index')->with('error', 'Free plan cannot be deleted.');
        }
        
        if($plan->delete() && $plan->features()->delete()) {
            return redirect()->route('admin.plans.index')->with('success', 'Plan deleted successfully.');
        }
        else{
            return redirect()->route('admin.plans.index')->with('error', 'Failed to delete plan.');
        }
    }
}
