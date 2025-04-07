<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of the plans.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            
            $plans = Plan::orderBy('id', 'desc')->get();
        } else {
            
            $plans = Plan::whereIn('id', function ($query) use ($user) {
                $query->select('plan_id')
                    ->from('user_plans')
                    ->where('user_id', $user->id);
            })->orderBy('id', 'desc')->get();
        }

        return view('plans.index', compact('plans'));
    }


    /**
     * Show the form for creating a new plan.
     */
    public function create()
    {
        return view('plans.form');
    }

    /**
     * Store a newly created plan in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan_name' => 'required',
            'plan_type' => 'required',
            // 'plan_format' => 'required|in:RAW,Cleaned',
            'duration' => 'required',
            'price' => 'required',
            'status' => 'required',
        ]);

        Plan::create($request->all());

        return redirect()->route('plans.index')->with('success', 'Plan created successfully...');
    }

    /**
     * Show the form for editing the specified plan.
     */
    public function edit(Plan $plan)
    {
        return view('plans.form', compact('plan'));
    }

    /**
     * Update the specified plan in storage.
     */
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'plan_name' => 'required',
            'plan_type' => 'required',
            'duration' => 'required',
            'price' => 'required',
            'status' => 'required',
        ]);

        $plan->update($request->all());

        return redirect()->route('plans.index')->with('success', 'Plan updated successfully...');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->route('plans.index')->with('success', 'Plan deleted successfully...');
    }
    
}