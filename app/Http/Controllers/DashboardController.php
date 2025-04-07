<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PlanUpload;
use App\Models\Plan;
use Carbon\Carbon;
use App\Models\UserPlan;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $userCount = User::count();
            $uploadCount = PlanUpload::count();
            $planCount = Plan::count();

            $recentUsers = User::with('userPlan.plan')->where('role', 'user')
                ->orderBy('created_at', 'desc') 
                ->limit(10)
                ->get();

            $planWiseUserCounts = DB::table('plans')
            ->leftJoin('user_plans', 'plans.id', '=', 'user_plans.plan_id')
            ->leftJoin('users', 'user_plans.user_id', '=', 'users.id') // Join through the pivot table
            ->select('plans.plan_name', DB::raw('count(DISTINCT users.id) as user_count')) // Count DISTINCT users
            ->groupBy('plans.plan_name')
            ->orderBy('plans.plan_name')
            ->get();
            
            return view('dashboard', compact('user', 'userCount', 'uploadCount', 'planCount' ,'planWiseUserCounts','recentUsers'));

        } else {

            $userCount = User::with('userPlan.plan')->where('id', $user->id)->count();
            $planCount = Plan::whereIn('id', function ($query) use ($user) {
                $query->select('plan_id')
                    ->from('user_plans')
                    ->where('user_id', $user->id);
            })->count();
            $planIds = Plan::pluck('id')->toArray();// Get all plan id.
            $uploadCount = PlanUpload::with('plan')
                ->whereHas('plan', function ($query) use ($user) {
                    $query->whereIn('id', function ($subQuery) use ($user) {
                        $subQuery->select('plan_id')
                            ->from('user_plans')
                            ->where('user_id', $user->id);
                    });
                })->count();
            return view('dashboard', compact('user', 'userCount','uploadCount','planCount'));

        }
    }

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    // public function sendResetLinkEmail(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email|exists:users,email',
    //     ]);

    //     $status = Password::sendResetLink($request->only('email'));

    //     return $status === Password::RESET_LINK_SENT
    //         ? back()->with('status', __($status))
    //         : back()->withErrors(['email' => __($status)]);
    // }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]); //Added withInput
    }

    public function showResetForm($token, Request $request)
    {
       
        $email = $request->query('email');
        return view('auth.reset-password', ['token' => $token, 'email' => $email]);
    }
    public function updatePassword(Request $request)
{
     $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|confirmed|min:4',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('login')->with('status', __($status)) // Redirect to login with success
        : back()->withErrors(['email' => __($status)]); // Stay on the reset page with errors
}

}
