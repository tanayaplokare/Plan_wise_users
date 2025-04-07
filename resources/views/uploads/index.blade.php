@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Original Uploaded Files</h2>
    <div class="mb-3 text-right">
        <a href="{{ route('uploads.create') }}" class="btn btn-primary">Upload New File</a>
    </div>

    @include('partials.alerts') {{-- Create a partial for success/error messages --}}

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Original Filename</th>
                <th>Uploaded At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($originalFiles as $file)
            <tr>
                <td>{{ $file->id }}</td>
                <td>{{ $file->original_filename }}</td>
                <td>{{ $file->created_at->format('d/m/Y H:i:s')}}</td>
                <td>
                    {{-- Link to start the filtering process for this file --}}
                    <a href="{{ route('uploads.filter.form', $file->id) }}" class="btn btn-sm btn-info" title="Scan/Filter">
                        <i class="fa fa-search"></i> Scan
                    </a>

                    {{-- Delete Original File --}}
                    <form action="{{ route('uploads.destroy', $file->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Delete original file {{ $file->original_filename }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Original">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No files uploaded yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    {{-- Pagination Links --}}
    {{ $originalFiles->links() }}
</div>
@endsection