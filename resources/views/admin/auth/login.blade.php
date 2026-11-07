<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | {{ \App\Models\Setting::get('app_name', 'DilSe Match') }}</title>
    <!-- Google Font: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box { width: 420px; }
        .login-card-body {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 10px 10px -5px rgba(0, 0, 0, 0.3);
            padding: 35px 30px;
        }
        .brand-logo {
            text-align: center;
            margin-bottom: 25px;
        }
        .brand-logo i {
            color: #f43f5e;
            font-size: 42px;
        }
        .brand-logo h2 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 10px;
            letter-spacing: -0.5px;
        }
        .btn-primary {
            background: #e11d48;
            border-color: #e11d48;
            font-weight: 600;
            padding: 10px;
            border-radius: 6px;
        }
        .btn-primary:hover {
            background: #be123c;
            border-color: #be123c;
        }
        .form-control { border-radius: 6px; }
        .input-group-text { border-radius: 0 6px 6px 0; background: #f8fafc; }
        .demo-credentials {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 12px 14px;
            margin-top: 20px;
            font-size: 12px;
            color: #475569;
        }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-card-body">
        <div class="brand-logo">
            <i class="fa-solid fa-heart-pulse"></i>
            <h2>{{ \App\Models\Setting::get('app_name', 'DilSe Match') }}</h2>
            <p class="text-muted text-sm mb-0">AdminLTE 3 Central Control Panel</p>
        </div>

        @if(session('info'))
            <div class="alert alert-info py-2 text-sm">{{ session('info') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-2 text-sm">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="post">
            @csrf
            <div class="input-group mb-3">
                <input type="email" name="email" class="form-control" placeholder="Admin Email" value="{{ old('email', 'admin@datingapp.com') }}" required autofocus>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-envelope text-muted"></span>
                    </div>
                </div>
            </div>

            <div class="input-group mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" value="Admin@123456" required>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock text-muted"></span>
                    </div>
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-8">
                    <div class="icheck-primary">
                        <input type="checkbox" id="remember" name="remember" checked>
                        <label for="remember" class="text-sm text-muted font-weight-normal ml-1">
                            Remember Me
                        </label>
                    </div>
                </div>
                <div class="col-4">
                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                </div>
            </div>
        </form>

        <div class="demo-credentials">
            <strong>Default Administrator Credentials:</strong><br>
            <span class="d-inline-block mt-1">Email: <code>admin@datingapp.com</code></span><br>
            <span>Password: <code>Admin@123456</code></span>
        </div>

        <div class="text-center mt-3">
            <a href="{{ url('/api/docs') }}" target="_blank" class="text-sm text-muted">
                <i class="fas fa-book mr-1"></i> Interactive REST API Documentation
            </a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
