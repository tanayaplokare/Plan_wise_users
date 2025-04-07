@extends('layouts.app')
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
{{-- Add Bootstrap 5 theme for Select2 for better styling integration --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
{{-- Or for Bootstrap 4 theme: --}}
{{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css"> --}}

<style>
    /* Ensure Select2 takes full width within its container */
    .select2-container {
        width: 100% !important;
        box-sizing: border-box; /* Include padding and border in the element's total width and height */
    }
    /* Adjust vertical alignment and height for Bootstrap if needed */
     .select2-container--bootstrap-5 .select2-selection--single {
        height: calc(1.5em + .75rem + 2px); /* Match default BS5 input height */
        padding: .375rem .75rem;
        display: flex;
        align-items: center;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple {
        min-height: calc(1.5em + .75rem + 2px); /* Match default BS5 input height */
        padding: .375rem .75rem;
    }
     .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        margin-top: 0.3125rem; /* Adjust vertical spacing of selections */
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        border-color: #86b7fe; /* Optional: match BS focus border color */
    }
</style>
@endpush
@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                  Filer the column
                </h4>
                <p>Original File: <strong>{{ $original_filename ?? basename($filename) }}</strong></p> {{-- Display original name --}}
                <div class="basic-form">
                    <form action="{{ route('process.filter') }}" 
                          method="POST">
                        @csrf
                        <input type="hidden" name="filename" value="{{ $filename }}">
                            {{-- Hidden field for the original filename (optional but useful) --}}
                        <input type="hidden" name="original_filename" value="{{ $original_filename ?? '' }}">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Select Column to Filter the records</label>
                                <select name="column" id="column" class="form-control"  required>
                                    <option value="">-- Select Column --</option>
                                    @foreach ($columns as $column)
                                        <option value="{{ $column }}">{{ $column }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="keywords">Select Keywords to Remove Rows</label>
                                {{-- Use a multi-select dropdown. Add 'multiple' attribute. --}}
                                {{-- The name="keywords[]" is crucial for sending an array --}}
                                <select name="keywords[]" id="keywords" class="form-control select2" multiple required size="8"> {{-- Adjust size as needed --}}
                                    {{-- Loop through keywords fetched from DB --}}
                                    @foreach ($keywords as $id => $keyword) {{-- Assuming $keywords is id=>keyword map --}}
                                     {{-- Or if $keywords is just an array of strings: @foreach ($keywords as $keyword) --}}
                                        {{-- Use old('keywords') to repopulate. It returns an array. --}}
                                        <option value="{{ $keyword }}" {{ (is_array(old('keywords')) && in_array($keyword, old('keywords'))) ? 'selected' : '' }}>
                                            {{ $keyword }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Select one or more keywords. Rows containing any of these keywords in the selected column will be removed.</small>
                            </div>
                        </div>
                           
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-dark">Filter and Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Ensure jQuery is loaded BEFORE Select2 --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> {{-- Or your project's jQuery --}}
{{-- Use the specific Select2 JS version requested --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

{{-- Select2 Initialization Script using the provided snippet --}}
<script>
    $(document).ready(function() {
        // Initialize all elements with the 'select2' class
        $('.select2').select2({
            placeholder: "Select an option", // Generic placeholder
            allowClear: true,
            width: '100%', // Ensure full width is applied
            theme: "bootstrap-5" // Apply the Bootstrap 5 theme
            // theme: "bootstrap" // Use this if you are on Bootstrap 4
        });

        // Optional: You can override placeholders for specific selects if needed
        $('#column').select2({
            placeholder: "-- Select Column --",
            allowClear: true, // Keep allowClear if needed for single select
            width: '100%',
            theme: "bootstrap-5"
        });

        $('#keywords').select2({
            placeholder: "Select keywords...",
            allowClear: true,
            width: '100%',
            theme: "bootstrap-5"
        });
    });
</script>
@endpush


