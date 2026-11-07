<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel') | {{ \App\Models\Setting::get('app_name', 'DilSe Match') }}</title>

    <!-- Google Font: Inter & Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style (AdminLTE 3.2.0) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        body { font-family: 'Inter', 'Source Sans Pro', sans-serif; font-size: 14px; }
        .main-sidebar { background: #111827; }
        .brand-link { border-bottom: 1px solid #1f2937; background: #0f172a; padding: 15px 16px; }
        .brand-link .brand-text { font-weight: 700; color: #fff; font-size: 16px; letter-spacing: -0.2px; }
        .brand-link i { color: #f43f5e; font-size: 22px; margin-right: 8px; }
        .nav-sidebar .nav-link { color: #94a3b8; border-radius: 6px; margin: 2px 8px; font-weight: 500; }
        .nav-sidebar .nav-link.active { background-color: #e11d48 !important; color: #fff !important; box-shadow: 0 4px 6px -1px rgba(225, 29, 72, 0.3); }
        .nav-sidebar .nav-link:hover { color: #fff; background: rgba(255,255,255,0.06); }
        .nav-header { color: #64748b !important; font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px; padding: 10px 16px 4px 16px; }
        .card { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); border-radius: 8px; border: 1px solid #e2e8f0; }
        .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; font-weight: 600; }
        .small-box { border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08); overflow: hidden; }
        .badge { font-weight: 600; padding: 5px 8px; border-radius: 4px; }
        .table th { background: #f8fafc; color: #475569; font-weight: 600; border-top: none; }
        .table td { vertical-align: middle; }
    </style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('admin.dashboard') }}" class="nav-link font-weight-bold">Dashboard</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ url('/api/docs') }}" target="_blank" class="nav-link"><i class="fas fa-book mr-1 text-primary"></i> API Docs</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('admin.demo-data.index') }}" class="nav-link text-warning"><i class="fas fa-database mr-1"></i> Demo Profiles</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    @php $pendingCnt = \App\Models\UserReport::where('status', 'pending')->count(); @endphp
                    @if($pendingCnt > 0)
                        <span class="badge badge-danger navbar-badge">{{ $pendingCnt }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">{{ $pendingCnt }} Pending Reports</span>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('admin.reports.index', ['status' => 'pending']) }}" class="dropdown-item">
                        <i class="fas fa-flag mr-2 text-danger"></i> {{ $pendingCnt }} user reports waiting review
                    </a>
                </div>
            </li>

            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::guard('admin')->user()->name ?? 'Admin') }}&background=f43f5e&color=fff" class="user-image img-circle elevation-1" alt="Admin">
                    <span class="d-none d-md-inline font-weight-bold">{{ Auth::guard('admin')->user()->name ?? 'Administrator' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <li class="user-header bg-dark text-center py-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::guard('admin')->user()->name ?? 'Admin') }}&background=f43f5e&color=fff" class="img-circle elevation-2" alt="Admin" style="width: 70px;">
                        <p class="mt-2 text-white font-weight-bold">
                            {{ Auth::guard('admin')->user()->name ?? 'Admin' }}
                            <small class="d-block text-muted">{{ ucfirst(str_replace('_', ' ', Auth::guard('admin')->user()->role ?? 'super_admin')) }}</small>
                        </p>
                    </li>
                    <li class="user-footer d-flex justify-content-between p-2">
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-default btn-flat btn-sm">Settings</a>
                        <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-flat btn-sm">Sign Out</button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-2">
        <a href="{{ route('admin.dashboard') }}" class="brand-link">
            <i class="fa-solid fa-heart-pulse"></i>
            <span class="brand-text">{{ \App\Models\Setting::get('app_name', 'DilSe Match') }}</span>
        </a>

        <div class="sidebar">
            <nav class="mt-3">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-gauge-high"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-header">USER MANAGEMENT</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Registered Users</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.verifications.index') }}" class="nav-link {{ request()->routeIs('admin.verifications.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-id-card"></i>
                            <p>Verification Requests</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.matches.index') }}" class="nav-link {{ request()->routeIs('admin.matches.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-heart"></i>
                            <p>Mutual Matches</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.matches.swipes') }}" class="nav-link {{ request()->routeIs('admin.matches.swipes') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-hand-pointer"></i>
                            <p>Swipe History Logs</p>
                        </a>
                    </li>

                    <li class="nav-header">MONETIZATION & PAYMENTS</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-credit-card"></i>
                            <p>Razorpay Transactions</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.subscriptions.index') }}" class="nav-link {{ request()->routeIs('admin.subscriptions.index') || request()->routeIs('admin.subscriptions.create') || request()->routeIs('admin.subscriptions.edit') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-crown"></i>
                            <p>Subscription Plans</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.subscriptions.users') }}" class="nav-link {{ request()->routeIs('admin.subscriptions.users') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-check"></i>
                            <p>User Subscriptions</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.stars.index') }}" class="nav-link {{ request()->routeIs('admin.stars.index') || request()->routeIs('admin.stars.create') || request()->routeIs('admin.stars.edit') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-star"></i>
                            <p>Star Packages</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.stars.transactions') }}" class="nav-link {{ request()->routeIs('admin.stars.transactions') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-receipt"></i>
                            <p>Star Wallet Logs</p>
                        </a>
                    </li>

                    <li class="nav-header">MODERATION & SAFETY</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shield-alt"></i>
                            <p>User Reports</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.demo-data.index') }}" class="nav-link {{ request()->routeIs('admin.demo-data.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-database"></i>
                            <p>Demo Data Manager</p>
                        </a>
                    </li>

                    <li class="nav-header">SYSTEM & SETTINGS</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-sliders"></i>
                            <p>System Settings</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.logs.index') }}" class="nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-clock-rotate-left"></i>
                            <p>Admin Audit Logs</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/api/docs') }}" target="_blank" class="nav-link">
                            <i class="nav-icon fas fa-code"></i>
                            <p>REST API Docs</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">@yield('page_title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle mr-2"></i> {{ session('info') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Please correct the following errors:</strong>
                        <ul class="mb-0 mt-1 pl-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @yield('content')
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer text-sm">
        <strong>Copyright &copy; {{ date('Y') }} {{ \App\Models\Setting::get('app_name', 'DilSe Match') }}.</strong> All rights reserved. Production-Ready Laravel Dating Backend.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('scripts')
</body>
</html>
