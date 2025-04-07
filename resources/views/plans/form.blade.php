@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    {{ isset($plan) ? 'Edit Plan : (' . $plan->plan_name.')' : 'Add Plan' }}
                </h4>
                <div class="basic-form">
                    <form action="{{ isset($plan) ? route('plans.update', $plan->id) : route('plans.store') }}" 
                          method="POST">
                        @csrf
                        @if(isset($plan))
                            @method('PUT')
                        @endif

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Plan Name</label>
                                <input type="text" name="plan_name" class="form-control" placeholder="Global" 
                                       value="{{ old('plan_name', $plan->plan_name ?? '') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Plan Type</label>
                                <input type="text" name="plan_type" class="form-control" placeholder="India" 
                                       value="{{ old('plan_type', $plan->plan_type ?? '') }}" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Duration</label>
                                <input type="text" name="duration" class="form-control" placeholder="Monthly" 
                                       value="{{ old('duration', $plan->duration ?? '') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Price</label>
                                <input type="number" name="price" class="form-control" placeholder="299.00" 
                                       value="{{ old('price', $plan->price ?? '') }}" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="active" {{ (isset($plan) && $plan->status == 'active') ? 'selected' : '' }}>Active</option>
                                    <option value="deactive" {{ (isset($plan) && $plan->status == 'deactive') ? 'selected' : '' }}>Deactive</option>
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
