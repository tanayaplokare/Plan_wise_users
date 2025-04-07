@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Filtered File Results</h2>

    @include('partials.alerts') {{-- Reuse alerts partial --}}

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>File</th>
                <th>Columns</th>
                <th>Keywords</th>
                <th>Filtered At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($filteredFiles as $file)
            <tr>
                <td>{{ $file->id }}</td>
                <td>{{ $file->originalUpload->original_filename ?? 'N/A' }}</td>
                <td>{{ is_array($file->filtered_column) ? implode(', ', $file->filtered_column) : 'N/A' }}</td>

                <td>{{ is_array($file->selected_keywords) ? implode(', ', $file->selected_keywords) : 'N/A' }}</td>
                <td>{{ $file->created_at->format('d/m/Y H:i:s')}}</td>
                <td>
                    <a href="{{ route('filtered.download', $file->id) }}" class="btn btn-sm btn-success" title="Download Filtered File">
                        <i class="fa fa-download"></i> Download
                    </a>

                    <form action="{{ route('filtered.destroy', $file->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Delete filtered result {{ basename($file->file_path) }}? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Filtered Result">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No filtered results found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    {{-- Pagination Links --}}
    {{ $filteredFiles->links() }}

    <div class="mt-3">
         <a href="{{ route('uploads.index') }}" class="btn btn-secondary">Back to Uploads</a>
    </div>
</div>
@endsection