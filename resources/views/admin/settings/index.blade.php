@extends('admin.layouts.app')

@section('title', 'Application Settings')
@section('page_title', 'Configuration & Business Rules')

@section('breadcrumb')
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
<div class="row">
    <!-- Settings Group Navigation Sidebar -->
    <div class="col-md-3 mb-3">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold"><i class="fas fa-sliders mr-1"></i> Settings Categories</h3>
            </div>
            <div class="card-body p-0">
                <div class="nav flex-column nav-pills p-2">
                    @foreach($groups as $grpKey => $grp)
                        <a href="{{ route('admin.settings.index', ['group' => $grpKey]) }}"
                           class="nav-link py-2 px-3 {{ $currentGroup === $grpKey ? 'active' : '' }}">
                            <i class="fas {{ $grp['icon'] }} mr-2"></i> {{ $grp['title'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Active Settings Group Form -->
    <div class="col-md-9">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">
                    <i class="fas {{ $groups[$currentGroup]['icon'] ?? 'fa-gear' }} text-primary mr-1"></i>
                    {{ $groups[$currentGroup]['title'] ?? 'Settings' }}
                </h3>
                <span class="badge badge-light border">Group: {{ $currentGroup }}</span>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="group" value="{{ $currentGroup }}">

                <div class="card-body">
                    @forelse($settings as $setting)
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark">
                                {{ $setting->label ?: ucfirst(str_replace('_', ' ', $setting->key)) }}
                            </label>

                            @if($setting->type === 'textarea')
                                <textarea name="{{ $setting->key }}" class="form-control" rows="3">{{ old($setting->key, $setting->value) }}</textarea>
                            @elseif($setting->type === 'boolean')
                                <select name="{{ $setting->key }}" class="form-control">
                                    <option value="1" {{ old($setting->key, $setting->value) == '1' ? 'selected' : '' }}>Enabled / Yes</option>
                                    <option value="0" {{ old($setting->key, $setting->value) == '0' ? 'selected' : '' }}>Disabled / No</option>
                                </select>
                            @elseif($setting->type === 'select')
                                @if($setting->key === 'razorpay_mode')
                                    <select name="{{ $setting->key }}" class="form-control">
                                        <option value="test" {{ old($setting->key, $setting->value) === 'test' ? 'selected' : '' }}>Test / Sandbox Mode</option>
                                        <option value="live" {{ old($setting->key, $setting->value) === 'live' ? 'selected' : '' }}>Live Production Gateway</option>
                                    </select>
                                @elseif($setting->key === 'distance_unit')
                                    <select name="{{ $setting->key }}" class="form-control">
                                        <option value="km" {{ old($setting->key, $setting->value) === 'km' ? 'selected' : '' }}>Kilometers (km)</option>
                                        <option value="miles" {{ old($setting->key, $setting->value) === 'miles' ? 'selected' : '' }}>Miles (mi)</option>
                                    </select>
                                @elseif($setting->key === 'otp_provider')
                                    <select name="{{ $setting->key }}" class="form-control">
                                        <option value="demo" {{ old($setting->key, $setting->value) === 'demo' ? 'selected' : '' }}>Demo Mode (Fixed OTP code 123456)</option>
                                        <option value="smtp" {{ old($setting->key, $setting->value) === 'smtp' ? 'selected' : '' }}>SMTP Email Delivery</option>
                                    </select>
                                @else
                                    <input type="text" name="{{ $setting->key }}" class="form-control" value="{{ old($setting->key, $setting->value) }}">
                                @endif
                            @elseif($setting->type === 'password')
                                <div class="input-group">
                                    <input type="password" name="{{ $setting->key }}" class="form-control" value="{{ old($setting->key, $setting->value) }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-default" type="button" onclick="togglePass(this)"><i class="fas fa-eye"></i></button>
                                    </div>
                                </div>
                            @elseif($setting->type === 'number')
                                <input type="number" name="{{ $setting->key }}" class="form-control" value="{{ old($setting->key, $setting->value) }}">
                            @else
                                <input type="text" name="{{ $setting->key }}" class="form-control" value="{{ old($setting->key, $setting->value) }}">
                            @endif

                            @if($setting->description)
                                <small class="form-text text-muted">{{ $setting->description }}</small>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-info-circle mb-2"></i> No configurable keys found in this group.
                        </div>
                    @endforelse
                </div>

                <div class="card-footer bg-white border-top d-flex justify-content-between">
                    <span class="text-muted text-xs align-self-center">Changes take effect immediately across all API responses.</span>
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePass(btn) {
        const input = btn.closest('.input-group').querySelector('input');
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
            input.type = 'password';
            btn.innerHTML = '<i class="fas fa-eye"></i>';
        }
    }
</script>
@endpush
