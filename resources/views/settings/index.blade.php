@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">

                <div>
                    <h4 class="card-title">Filter Files</h4>
                    {{-- Keep your existing admin check for uploading --}}
                    @if(auth()->user()->role === 'admin')
                    <div class="mb-3 text-right">
                        <a href="{{ route('setting.upload_form') }}" class="btn btn-primary">Upload File</a>
                    </div>
                    @endif
                </div>
                <hr>
                {{-- Session Messages for Success/Error --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                 @if ($errors->any()) {{-- Display validation or other errors --}}
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                <div class="table-responsive">
                    <table class="table header-border table-hover"> {{-- Added table-hover for better UI --}}
                        <thead>
                            <tr>
                                <th>#Sr.No.</th>
                                <th>Original Filename</th>
                                <th>Filtered Column</th>
                                <th>Keywords</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($filteredFiles as $key => $file)
                                <tr>
                                    <td>{{ $loop->iteration }}</td> 
                                    <td>{{ $file->original_filename ?? basename($file->file_path) }}</td> 
                                    <td>{{ $file->filtered_column }}</td>
                                    <td>
                                        {{-- Display selected keywords --}}
                                        @if(is_array($file->selected_keywords) && !empty($file->selected_keywords))
                                            {{ implode(', ', $file->selected_keywords) }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $file->created_at->format('d/m/Y H:i:s')}}</td> {{-- Show time too --}}
                                    <td>
                                        {{-- Download Link --}}
                                        <a href="{{ route('download.filtered.file', $file->id) }}" class="btn btn-sm btn-success" title="Download">
                                           <i class="fa fa-download"></i> Download {{-- Example using FontAwesome --}}
                                        </a>

                                        {{-- Delete Button Form --}}
                                        {{-- Add admin check if only admins can delete --}}
                                        @if(auth()->user()->role === 'admin')
                                            <form action="{{ route('filtered.uploads.destroy', $file->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this record and its file?');">
                                                @csrf
                                                @method('DELETE') {{-- Important: Use DELETE method --}}
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fa fa-trash"></i> Delete {{-- Example using FontAwesome --}}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    {{-- Adjust colspan based on the final number of columns --}}
                                    <td colspan="6" class="text-center">No filtered files found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
{{-- Optional: Add FontAwesome if you use icons --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endpush