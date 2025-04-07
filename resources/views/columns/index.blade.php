@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Columns</h4>
                <div class="d-flex justify-content-end mb-3"> 
                    <a href="{{ route('columns.create') }}" class="btn btn-primary">Add Columns</a>
                </div>
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-striped table-bordered zero-configuration">
                        <thead>
                            <tr>
                                <th>#Sr.No</th>
                                <th>Columns</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($columns as $key=>$column)
                                <tr>
                                    <td>{{ $key +1 }}</td>
                                    <td>{{ $column->column }}</td>
                                    <td>{{ ucwords($column->status) }}</td>
                                    <td>{{ date('d/m/Y', strtotime($column->created_at))}}</td>
                                    <td>
                                        {{-- <a href="{{ route('columns.show', $column) }}" class="btn btn-info btn-sm">View</a> --}}
                                        <a href="{{ route('columns.edit', $column) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('columns.destroy', $column) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Srno</th>
                                <th>Column</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection