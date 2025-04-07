<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserPlan;
use App\Models\Plan;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            
            $users = User::with('userPlan.plan')->orderBy('id', 'desc')->get();
        } else {
          
            $users = User::with('userPlan.plan')->where('id', $user->id)->get();
        }

        return view('users.index', compact('users'));
    }


    public function create()
    {
        $plans = Plan::where('status' ,'active')->get();
        return view('users.form', compact('plans'));
    }

    public function store(Request $request)
    {
       
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'plan_id' => 'required|exists:plans,id'
        ], [
            'email.unique' => 'This email is already registered. Please use a different email.',
            'email.required' => 'Email field is required.', 
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'mobile' => !empty($request->mobile) ? $request->mobile : '',
        ]);

        UserPlan::create([
            'user_id' => $user->id,
            'plan_id' => $request->plan_id,
            'status' => $request->status
        ]);

        return redirect()->route('users.index')->with('success', 'User added successfully!');
    }

    public function edit(User $user)
    {
        $plans = Plan::where('status' ,'active')->get();
        return view('users.form', compact('user', 'plans'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'password' => 'nullable',
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'mobile' => !empty($request->mobile) ? $request->mobile : '',
        ]);

        $user->userPlan()->updateOrCreate(
            ['user_id' => $user->id],
            ['plan_id' => $request->plan_id, 'status' => $request->status]
        );

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->userPlan) {
            $user->userPlan()->delete();
        }
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User and associated plan deleted successfully!');
    }

}
