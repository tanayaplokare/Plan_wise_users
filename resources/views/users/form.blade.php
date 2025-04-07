@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    {{ isset($user) ? 'Edit User Plan : (' . $user->name.')' : 'Assign User Plan' }}
                </h4>
                <div class="basic-form">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                    <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" 
                          method="POST">
                        @csrf
                        @if(isset($user))
                            @method('PUT')
                        @endif

                       
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" placeholder="John Doe" 
                                       value="{{ old('name', $user->name ?? '') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" placeholder="john@gmail.com" 
                                       value="{{ old('email', $user->email ?? '') }}" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Password" 
                                       value="{{ old('password', $user->password ?? '') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Plan</label>
                                <select name="plan_id" class="form-control" required>
                                    <option value="">Select Plan</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}" 
                                            {{ old('plan_id', $user->userPlan->plan_id ?? '') == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->plan_name }} - ${{ $plan->price }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                        </div>

                        <div class="form-row">

                            <div class="form-group col-md-6">
                                <label>Mobile</label>
                                <input type="text" name="mobile" class="form-control" placeholder="9433678907" 
                                       value="{{ old('mobile', $user->mobile ?? '') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="active" {{ isset($user) && optional($user->userPlan)->status == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="deactive" {{ isset($user) && optional($user->userPlan)->status == 'deactive' ? 'selected' : '' }}>Deactive</option>
                                </select>
                            </div>
                        </div>
                        
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-dark">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
