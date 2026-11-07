@extends('admin.layouts.app')

@section('title', 'Demo Data Manager')
@section('page_title', 'Demo Profiles & Sandbox Data Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Demo Data</li>
@endsection

@section('content')
<!-- Metric Cards -->
<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="small-box bg-secondary shadow-sm">
            <div class="inner">
                <h3>{{ number_format($demoUserCount) }}</h3>
                <p>Total Demo Profiles (is_demo = 1)</p>
            </div>
            <div class="icon"><i class="fas fa-users-gear"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="small-box bg-primary shadow-sm">
            <div class="inner">
                <h3>{{ number_format($maleDemoCount) }}</h3>
                <p>Indian Male Demo Profiles</p>
            </div>
            <div class="icon"><i class="fas fa-mars"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="small-box bg-pink shadow-sm" style="background:#ec4899; color:#fff;">
            <div class="inner">
                <h3>{{ number_format($femaleDemoCount) }}</h3>
                <p>Indian Female Demo Profiles</p>
            </div>
            <div class="icon"><i class="fas fa-venus"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="small-box bg-success shadow-sm">
            <div class="inner">
                <h3>{{ number_format($realUserCount) }}</h3>
                <p>Real Production Users</p>
            </div>
            <div class="icon"><i class="fas fa-user-check"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Generator Section -->
    <div class="col-md-6 mb-4">
        <div class="card card-outline card-primary shadow-sm h-100">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-wand-magic-sparkles text-primary mr-1"></i> Generate Indian Demo Accounts
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Generate realistic Indian male and female profiles populated with authentic names, verified bios, occupation, education, categorized hobbies, high-resolution portrait photos, and GPS coordinates across major Indian metropolitan areas (Delhi NCR, Mumbai, Bangalore, Hyderabad, Pune, Kolkata, Chennai, Jaipur, Goa, etc.).
                </p>

                <form action="{{ route('admin.demo-data.generate') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Number of Demo Profiles to Seed</label>
                        <input type="number" name="count" class="form-control" value="2020" min="10" max="3000" required>
                        <small class="text-muted">Default requirement: ~2,020 profiles.</small>
                    </div>

                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fas fa-play mr-1"></i> Generate Demo Accounts
                    </button>
                </form>

                <hr class="my-4">

                <h5 class="font-weight-bold">Generate Demo Activity (Swipes & Matches)</h5>
                <p class="text-muted text-sm">Create simulated mutual matches, swipe interactions, and sample Razorpay orders between demo accounts to test recommendation engines and charts.</p>
                <form action="{{ route('admin.demo-data.activity') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-info font-weight-bold">
                        <i class="fas fa-bolt mr-1"></i> Seed Simulated Swipes & Matches
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Purge Section (Before Production) -->
    <div class="col-md-6 mb-4">
        <div class="card card-outline card-danger shadow-sm h-100">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-trash-can mr-1"></i> Clean Production Preparation (Purge Demo Data)
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning text-sm mb-3">
                    <i class="fas fa-triangle-exclamation mr-1"></i>
                    <strong>Caution:</strong> This irreversible action will cleanly wipe all <code>{{ number_format($demoUserCount) }}</code> demo accounts, their photos, locations, swipes, and matches. Real registered users (<code>{{ number_format($realUserCount) }}</code>) will not be affected.
                </div>

                <p class="text-muted">
                    Before deploying to live cPanel hosting or launching to real users on the Play Store / App Store, use this button to permanently purge all sample data.
                </p>

                <form action="{{ route('admin.demo-data.purge') }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete ALL demo profiles?');">
                    @csrf
                    <div class="form-group">
                        <label class="text-danger font-weight-bold">Type "DELETE DEMO DATA" to confirm:</label>
                        <input type="text" name="confirm_purge" class="form-control border-danger" placeholder="DELETE DEMO DATA" required>
                    </div>

                    <button type="submit" class="btn btn-danger font-weight-bold">
                        <i class="fas fa-dumpster-fire mr-1"></i> Permanently Purge All Demo Profiles
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
