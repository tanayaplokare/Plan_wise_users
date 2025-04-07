@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
              
                <div>
                    <h4 class="card-title">Plan-Wise Download File</h4>
                    @if(auth()->user()->role === 'admin')
                    <div class="mb-3 text-right">
                        <a href="{{ route('planuploads.create') }}" class="btn btn-primary">Upload File</a>
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
                                <th>Plan</th>
                                {{-- <th>Original Filename</th> --}}
                                <th>Filename</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($uploads->isNotEmpty())
                                @foreach($uploads as $key=> $upload)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $upload->plan->plan_name }} - {{ $upload->plan->plan_type }}</td>
                                        {{-- <td>{{ $upload->original_filename }}</td> --}}
                                        <td>{{ $upload->stored_filename }}</td>
                                        <td>
                                            <a href="{{ route('planuploads.download', $upload->id) }}" class="btn btn-sm btn-success">Download</a>
                                            @if(auth()->user()->role === 'admin')
                                            <form action="{{ route('planuploads.destroy', $upload->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this file?')">Delete</button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No upload files found.</td>
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