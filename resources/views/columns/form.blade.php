@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    {{ isset($column) ? 'Edit Column : (' . $column->column.')' : 'Add Column' }}
                </h4>
                <div class="basic-form">
                    <form action="{{ isset($column) ? route('columns.update', $column->id) : route('columns.store') }}" 
                          method="POST">
                        @csrf
                        @if(isset($column))
                            @method('PUT')
                        @endif

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Column</label>
                                <input type="text" name="column" class="form-control" placeholder="column" 
                                       value="{{ old('column', $column->column ?? '') }}" required>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="active" {{ (isset($column) && $column->status == 'active') ? 'selected' : '' }}>Active</option>
                                    <option value="deactive" {{ (isset($column) && $column->status == 'deactive') ? 'selected' : '' }}>Deactive</option>
                                </select>
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
