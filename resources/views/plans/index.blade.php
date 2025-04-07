@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div>
                    <h4 class="card-title">Plans</h4>
                    @if(auth()->user()->role === 'admin')
                        <div class="mb-3 text-right">
                            <a href="{{ route('plans.create') }}" class="btn btn-primary">Add Plan</a>
                        </div>
                    @endif
                </div>
                <hr>
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="table-responsive">

                    <table class="table header-border">
                        <thead>
                            <tr>
                                <th>#Sr.No.</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Status</th>
                                @if(auth()->user()->role === 'admin')
                                <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if($plans->isNotEmpty())
                                @foreach($plans as $key=> $plan)
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $plan->plan_name }}</td>
                                        <td>{{ $plan->plan_type }}</td>
                                        <td>{{ $plan->duration }}</td>
                                        <td>{{ $plan->price }}</td>
                                        <td>{{ ucwords($plan->status) }}</td>
                                        @if(auth()->user()->role === 'admin')
                                        <td>
                                        <a href="{{ route('plans.edit', $plan->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('plans.destroy', $plan->id) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete plan ?')">Delete</button>
                                        </form>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">No plans found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection