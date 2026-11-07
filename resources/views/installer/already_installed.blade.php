<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer Locked | Dating Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }
        .lock-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            max-width: 520px;
            width: 100%;
            padding: 40px 30px;
            text-align: center;
        }
        .lock-icon {
            font-size: 52px;
            color: #e11d48;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="lock-card">
    <div class="lock-icon">
        <i class="fas fa-lock"></i>
    </div>
    <h3 class="font-weight-bold text-dark">Installer is Locked</h3>
    <p class="text-muted text-sm mt-2 mb-4">
        This application has already been installed. For security purposes, the installation wizard is permanently disabled.
    </p>
    <div class="alert alert-secondary text-xs text-left">
        <strong>Security Notice:</strong> To re-run the installer, you must manually delete the lock file at: <code>storage/installed.lock</code>.
    </div>
    <div class="mt-4">
        <a href="{{ route('admin.login') }}" class="btn btn-danger font-weight-bold px-4" style="background:#e11d48; border-color:#e11d48;">
            <i class="fas fa-gauge-high mr-1"></i> Proceed to Admin Login
        </a>
    </div>
</div>

</body>
</html>
