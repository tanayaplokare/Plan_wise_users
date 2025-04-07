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
                    <form action="{{ route('upload.file') }}" 
                          method="POST" enctype="multipart/form-data">
                        @csrf
                       
                        {{-- <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Select File</label>
                                <input type="file" name="file" required>
                                <p><small>Ensure the file is in CSV format. Max upload size: {{ ini_get('upload_max_filesize') }}</small></p>
                            </div>
                            
                            
                        </div> --}}

                        <div class="form-row">
                            <div class="form-group col-md-12"> {{-- Make it full width or adjust as needed --}}
                                <label for="file">Select File</label>
                                <input type="file" name="file" id="file" class="form-control-file" required
                                       accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, text/plain">
                                {{-- Info about allowed types and size --}}
                                <p><small>Allowed types: CSV, XLSX, XLS. Max upload size: {{ ini_get('upload_max_filesize') }}</small></p>
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
