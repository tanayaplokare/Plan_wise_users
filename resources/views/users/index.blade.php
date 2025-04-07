@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
              
                <div>
                    <h4 class="card-title">Users</h4>
                    @if(auth()->user()->role === 'admin')
                    <div class="mb-3 text-right">
                        <a href="{{ route('users.create') }}" class="btn btn-primary">Assign User Plan</a>
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
                    <table id="usersTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#Sr.No.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Plan</th>
                                <th>Role</th>
                                <th>Status</th>
                                @if(auth()->user()->role === 'admin')
                                <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $key => $user)
                                <tr>
                                    <td>{{ $key +1 }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if(isset($user->userPlan) && isset($user->userPlan->plan))
                                            {{ $user->userPlan->plan->plan_name }} 
                                        @else
                                            No plan
                                        @endif
                                    </td>
                                    <td>{{ ucwords($user->role) }}</td>
                                    <td>
                                        @isset($user->userPlan)
                                            {{ ucwords($user->userPlan->status) }}
                                        @else
                                            No Status Assigned
                                        @endisset
                                    </td>
                                    @if(auth()->user()->role === 'admin')
                                    <td>
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user and associated plan?')">Delete</button>
                                        </form>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<!-- jQuery and DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            "processing": true,
            "serverSide": false, // Change to true if using Ajax
            "paging": true, // Enables pagination
            "searching": true, // Enables search
            "ordering": true, // Enables sorting
            "info": true, // Show info
            "lengthMenu": [10, 25, 50, 100], // Dropdown for entries
            "language": {
                "emptyTable": "No users found"
            }
        });
    });
</script>
@endsection

