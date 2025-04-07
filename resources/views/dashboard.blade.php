@extends('layouts.app')

@section('content')

<div class="row page-titles">
    <div class="col p-0">
        <h4>Welcome, <span> {{ Auth::user()->name }}</span></h4>
    </div>
    <div class="col p-0">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a>
            </li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </div>
</div>

<div class="row">
    @if(Auth::user()->role === 'admin')
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body bg-primary">
                    <div class="text-center">
                        <h2 class="m-t-15 text-white f-s-50">{{ $userCount }}</h2><span class="text-white m-t-10 f-s-20">All Users</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body bg-success">
                    <div class="text-center">
                        <h2 class="m-t-15 text-white f-s-50">{{ $uploadCount }}</h2><span class="text-white m-t-10 f-s-20">Download Files</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body bg-warning">
                    <div class="text-center">
                        <h2 class="m-t-15 text-white f-s-50">{{ $planCount }}</h2><span class="text-white m-t-10 f-s-20">Active Plans</span>
                    </div>
                </div>
            </div>
        </div>
    @else
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body bg-success">
                <div class="text-center">
                    <h2 class="m-t-15 text-white f-s-50">{{ $uploadCount }}</h2><span class="text-white m-t-10 f-s-20">Downloads</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body bg-warning">
                <div class="text-center">
                    <h2 class="m-t-15 text-white f-s-50">{{ $planCount }}</h2><span class="text-white m-t-10 f-s-20">Plans</span>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@if(Auth::user()->role === 'admin')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    @forelse ($planWiseUserCounts as $plan)
                    <div class="col text-center">
                        {{-- <div class="text-warning"><i class="fa fa-caret-down"></i> <span>-30%</span>
                        </div> --}}
                        <h2 class="m-b-0">{{ $plan->plan_name }}</h2>
                        <p class="text-uppercase" style="font-weight: bold;">{{ $plan->user_count }}</p>
                    </div>
                   
                    @empty
                        <div class="col">
                            <p>No plans found.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
   
</div>
@endif

@if(Auth::user()->role === 'admin')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Last 10 Top Active Members</h4>
                <div class="table-responsive">
                    <table class="table verticle-middle">
                        <thead>
                            <tr>
                                <th scope="col">#Sr.No.</th>
                                <th scope="col">Name</th>
                                <th scope="col">email</th>
                                <th scope="col">Mobile</th>
                                <th scope="col">Plan</th>
                                <th scope="col">Status</th>
                               
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentUsers as $key=>$recentUser)
                            <tr>
                                <td>{{ $key +1 }}</td>
                                <td>{{ $recentUser->name }}</td>
                                <td>{{ $recentUser->email }}</td>
                                <td>{{ !empty($recentUser->mobile) ? $recentUser->mobile : '-' }}</td>
                                <td>
                                    @if(isset($recentUser->userPlan) && isset($recentUser->userPlan->plan))
                                        {{ $recentUser->userPlan->plan->plan_name }} 
                                    @else
                                        No plan
                                    @endif
                                </td>
                                <td>
                                    @isset($recentUser->userPlan)
                                        {{ ucwords($recentUser->userPlan->status) }}
                                    @else
                                        No Status Assigned
                                    @endisset
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">No users found in the last 10 days.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif


@endsection