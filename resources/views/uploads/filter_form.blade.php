@extends('layouts.app')

@push('styles')
{{-- Keep Select2 CSS if used elsewhere, otherwise remove --}}
{{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}
{{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" /> --}}

<style>
    /* Style for the custom multiselect listbox */
    .custom-multiselect-container {
        border: 1px solid #ced4da; /* Bootstrap input border color */
        border-radius: 0.375rem; /* Bootstrap border radius */
        padding: 0.375rem 0; /* Adjust vertical padding */
        max-height: 200px; /* Set a max height for scrollability */
        overflow-y: auto; /* Enable vertical scrolling */
        background-color: #fff; /* White background */
    }

    .custom-multiselect-item {
        display: block; /* Make label take full width */
        padding: 0.375rem 0.75rem; /* Match Bootstrap input padding */
        cursor: pointer;
        white-space: nowrap; /* Prevent text wrapping */
    }

    .custom-multiselect-item:hover {
        background-color: #e9ecef; /* Light grey background on hover */
    }

    .custom-multiselect-item input[type="checkbox"] {
        margin-right: 0.5rem; /* Space between checkbox and text */
        vertical-align: middle; /* Align checkbox nicely with text */
        cursor: pointer;
    }

    .custom-multiselect-item label {
        cursor: pointer;
        display: inline; /* Allow label text to flow */
        vertical-align: middle; /* Align label text nicely */
        margin-bottom: 0; /* Override default Bootstrap label margin */
        font-weight: normal; /* Ensure label text isn't bold by default */
    }

    /* Style for Select All */
    .select-all-container {
        padding: 0.25rem 0.75rem; /* Add some padding */
        border-bottom: 1px solid #eee; /* Separator */
        margin-bottom: 0.25rem; /* Space below */
    }
     .select-all-container label {
        margin-bottom: 0; /* Align label nicely */
        margin-left: 0.5rem;
        cursor: pointer;
        font-weight: 500; /* Slightly bolder */
     }
     .select-all-container input[type="checkbox"]{
        vertical-align: middle;
        cursor: pointer;
     }


    /* Optional: Style for invalid state */
    .custom-multiselect-container.is-invalid {
        border-color: #dc3545; /* Bootstrap danger color */
    }
    /* Adjust invalid feedback position if Select All is present */
    .invalid-feedback {
        display: block; /* Make sure feedback is visible */
        width: 100%;
        margin-top: 0.25rem;
        font-size: .875em;
        color: #dc3545;
    }

</style>
@endpush

@section('content')
<div class="container">
    <h2>Filter File: {{ $originalUpload->original_filename }}</h2>

    @include('partials.alerts')

    <form action="{{ route('filter.process') }}" method="POST">
        @csrf
        <input type="hidden" name="original_upload_id" value="{{ $originalUpload->id }}">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="columns_list">Select Columns to Filter By</label>
                {{-- Add is-invalid class conditionally for styling --}}
                <div class="custom-multiselect-wrapper border @error('column') border-danger @enderror @error('column.*') border-danger @enderror rounded"> {{-- Optional wrapper for border--}}
                    {{-- Select All Checkbox for Columns --}}
                    <div class="select-all-container">
                        <input type="checkbox" id="select_all_columns">
                        <label for="select_all_columns">Select All</label>
                    </div>

                    <div id="columns_list" class="custom-multiselect-container" style="border: none; border-radius: 0 0 0.375rem 0.375rem;"> {{-- Remove border here if wrapper used --}}
                        {{-- Loop through headers to create checkboxes --}}
                        @forelse ($headers as $index => $header)
                            <div class="custom-multiselect-item">
                                <input type="checkbox"
                                       class="column-checkbox" {{-- Add class for JS targeting --}}
                                       name="column[]" {{-- Use array name --}}
                                       value="{{ $header }}"
                                       id="column_{{ $index }}" {{-- Unique ID for label 'for' --}}
                                       {{-- Check old input or default selections --}}
                                       {{ (is_array(old('column')) && in_array($header, old('column'))) ? 'checked' : '' }}>
                                <label for="column_{{ $index }}">{{ $header }}</label>
                            </div>
                        @empty
                            <div class="custom-multiselect-item text-muted">No columns found.</div>
                        @endforelse
                    </div>
                </div> {{-- End wrapper --}}
                {{-- Display validation errors --}}
                @error('column') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @error('column.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="keywords_list">Select Keywords (Rows containing any will be removed)</label>
                 <div class="custom-multiselect-wrapper border @error('keywords') border-danger @enderror @error('keywords.*') border-danger @enderror rounded"> {{-- Optional wrapper --}}
                    <div class="select-all-container">
                        <input type="checkbox" id="select_all_keywords">
                        <label for="select_all_keywords">Select All</label>
                    </div>

                    <div id="keywords_list" class="custom-multiselect-container" style="border: none; border-radius: 0 0 0.375rem 0.375rem;"> {{-- Remove border here if wrapper used --}}
                        @forelse ($keywords as $index => $keyword)
                            <div class="custom-multiselect-item">
                                <input type="checkbox"
                                       class="keyword-checkbox" 
                                       name="keywords[]" 
                                       value="{{ $keyword }}"
                                       id="keyword_{{ $index }}" 

                                       {{ (is_array(old('keywords')) && in_array($keyword, old('keywords'))) ? 'checked' : '' }}>
                                <label for="keyword_{{ $index }}">{{ $keyword }}</label>
                            </div>
                        @empty
                            <div class="custom-multiselect-item text-muted">No keywords found.</div>
                        @endforelse
                    </div>
                 </div> {{-- End wrapper --}}
                 {{-- Display validation errors --}}
                 @error('keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
                 @error('keywords.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Start Filtering</button>
            <a href="{{ route('uploads.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
{{-- Keep jQuery if needed elsewhere --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

{{-- Remove Select2 JS if no longer needed --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}

<script>
$(document).ready(function() {

    // --- Helper function to update 'Select All' checkbox state ---
    function updateSelectAllState(listContainerId, selectAllCheckboxId) {
        const $listContainer = $('#' + listContainerId);
        const $selectAllCheckbox = $('#' + selectAllCheckboxId);
        const $itemCheckboxes = $listContainer.find('input[type="checkbox"]'); // Find checkboxes within the specific list

        // If there are no items, disable 'Select All'
        if ($itemCheckboxes.length === 0) {
            $selectAllCheckbox.prop('checked', false);
            $selectAllCheckbox.prop('disabled', true);
            return;
        } else {
             $selectAllCheckbox.prop('disabled', false);
        }

        const totalItems = $itemCheckboxes.length;
        const checkedItems = $listContainer.find('input[type="checkbox"]:checked').length; // Count checked only within the specific list

        // Check 'Select All' if all items are checked, uncheck otherwise
        $selectAllCheckbox.prop('checked', totalItems === checkedItems);
    }

    // --- Columns List Logic ---
    // 1. 'Select All' for Columns
    $('#select_all_columns').on('change', function() {
        const isChecked = $(this).prop('checked');
        // Find checkboxes only within the columns list
        $('#columns_list').find('.column-checkbox').prop('checked', isChecked);
    });

    // 2. Individual Column Checkbox change
    $('#columns_list').on('change', '.column-checkbox', function() {
        updateSelectAllState('columns_list', 'select_all_columns');
    });

    // --- Keywords List Logic ---
    // 1. 'Select All' for Keywords
    $('#select_all_keywords').on('change', function() {
        const isChecked = $(this).prop('checked');
         // Find checkboxes only within the keywords list
        $('#keywords_list').find('.keyword-checkbox').prop('checked', isChecked);
    });

    // 2. Individual Keyword Checkbox change
    $('#keywords_list').on('change', '.keyword-checkbox', function() {
        updateSelectAllState('keywords_list', 'select_all_keywords');
    });

    // --- Initial State Check on Page Load ---
    updateSelectAllState('columns_list', 'select_all_columns');
    updateSelectAllState('keywords_list', 'select_all_keywords');

});
</script>
@endpush