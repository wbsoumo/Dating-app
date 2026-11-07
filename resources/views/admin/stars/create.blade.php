@extends('admin.layouts.app')

@section('title', 'Add Star Package')
@section('page_title', 'Create Star Package')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.stars.index') }}">Star Packages</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-star text-warning mr-1"></i> Star Package Details</h3>
            </div>

            <form action="{{ route('admin.stars.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Package Title <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Superstar Pack" value="{{ old('name') }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Stars Count <span class="text-danger">*</span></label>
                            <input type="number" name="stars_count" class="form-control" placeholder="60" value="{{ old('stars_count', 25) }}" min="1" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="399.00" value="{{ old('price', 399) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Currency <span class="text-danger">*</span></label>
                            <input type="text" name="currency" class="form-control" value="{{ old('currency', 'INR') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Discount Percentage (%)</label>
                            <input type="number" name="discount_percent" class="form-control" placeholder="20" value="{{ old('discount_percent', 0) }}" min="0" max="100">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Promotional Badge</label>
                        <input type="text" name="badge" class="form-control" placeholder="e.g. MOST POPULAR, BEST VALUE, 20% OFF" value="{{ old('badge') }}">
                    </div>

                    <div class="custom-control custom-switch mt-3">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                        <label class="custom-control-label font-weight-bold" for="is_active">Package Active for Purchase</label>
                    </div>
                </div>

                <div class="card-footer bg-white border-top d-flex justify-content-between">
                    <a href="{{ route('admin.stars.index') }}" class="btn btn-default">Cancel</a>
                    <button type="submit" class="btn btn-warning font-weight-bold">Save Package</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
