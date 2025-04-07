@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Keywords</h4>
                <div class="d-flex justify-content-end mb-3"> 
                    <a href="{{ route('keywords.create') }}" class="btn btn-primary">Add Keyword</a>
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
                                <th>Keyword</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($keywords as $key=>$keyword)
                                <tr>
                                    <td>{{ $key +1 }}</td>
                                    <td>{{ $keyword->keyword }}</td>
                                    <td>{{ ucwords($keyword->status) }}</td>
                                    <td>{{ date('d/m/Y', strtotime($keyword->created_at))}}</td>
                                    <td>
                                        {{-- <a href="{{ route('keywords.show', $keyword) }}" class="btn btn-info btn-sm">View</a> --}}
                                        <a href="{{ route('keywords.edit', $keyword) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('keywords.destroy', $keyword) }}" method="POST" style="display: inline-block;">
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
                                <th>Keyword</th>
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