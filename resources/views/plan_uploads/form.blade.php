@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                   Upload File
                </h4>
                <div class="basic-form">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{route('planuploads.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Plan Name</label>
                                <select name="plan_id" class="form-control" required>
                                    <option value="">Select Plan</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}" 
                                            {{ old('plan_id') }}>
                                            {{ $plan->plan_name }} - ${{ $plan->price }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                                <div class="form-group col-md-6">
                                    <label>Upload file(Max 20 MB)</label>
                                    <input type="file" name="file" class="form-control" required>
                                </div>
                        </div>

                        <div class="form-row">
                            
                            <div class="form-group col-md-6">
                                <label>Select Date</label>
                                <input type="date" name="upload_date" class="form-control" placeholder="299.00" 
                                       value="{{ old('upload_date') }}" required>
                                       {{-- <input type="text" class="form-control mydatepicker" placeholder="mm/dd/yyyy"> 
                                       <span class="input-group-append">
                                        </span> --}}
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-dark">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<!-- jQuery (Required for Bootstrap Datepicker) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap Datepicker -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<script>
    $(document).ready(function() {
        console.log("Initializing Datepicker..."); // Debugging Log

        // Initialize Datepicker
        $('.mydatepicker').datepicker({
            format: 'mm/dd/yyyy',  // Change format as needed ('yyyy-mm-dd' for DB storage)
            autoclose: true,
            todayHighlight: true
        });
    });
</script>
@endsection

