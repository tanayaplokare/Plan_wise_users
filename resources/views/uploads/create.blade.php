@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Upload New File</h2>

    @include('partials.alerts')

    <form action="{{ route('uploads.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group mb-3">
            <label for="file">Select File (CSV, XLSX, XLS, TXT)</label>
            <input type="file" name="file" id="file" class="form-control-file @error('file') is-invalid @enderror" required accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, .txt">
            @error('file')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Max file size: 10MB.</small>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
        <a href="{{ route('uploads.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection