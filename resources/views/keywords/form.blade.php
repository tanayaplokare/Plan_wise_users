@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    {{ isset($keyword) ? 'Edit Keyword : (' . $keyword->plan_name.')' : 'Add Keyword' }}
                </h4>
                <div class="basic-form">
                    <form action="{{ isset($keyword) ? route('keywords.update', $keyword->id) : route('keywords.store') }}" 
                          method="POST">
                        @csrf
                        @if(isset($keyword))
                            @method('PUT')
                        @endif

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Keyword</label>
                                <input type="text" name="keyword" class="form-control" placeholder="keyword" 
                                       value="{{ old('keyword', $keyword->keyword ?? '') }}" required>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="active" {{ (isset($keyword) && $keyword->status == 'active') ? 'selected' : '' }}>Active</option>
                                    <option value="deactive" {{ (isset($keyword) && $keyword->status == 'deactive') ? 'selected' : '' }}>Deactive</option>
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
