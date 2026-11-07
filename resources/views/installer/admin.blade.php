<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Setup | Dating Platform Installer</title>
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
        .installer-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            max-width: 680px;
            width: 100%;
            overflow: hidden;
        }
        .installer-header {
            background: #0f172a;
            color: #fff;
            padding: 25px 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .installer-header i { font-size: 32px; color: #f43f5e; }
        .installer-body { padding: 30px; }
        .step-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            position: relative;
        }
        .step-item {
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            flex: 1;
        }
        .step-item.active { color: #e11d48; }
        .step-circle {
            width: 28px; height: 28px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 6px auto;
            font-size: 12px;
        }
        .step-item.active .step-circle {
            background: #e11d48;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="installer-card">
    <div class="installer-header">
        <i class="fa-solid fa-heart-pulse"></i>
        <div>
            <h3 class="mb-0 font-weight-bold" style="font-size: 20px;">Admin Account & Demo Data</h3>
            <small style="color: #94a3b8;">Final Step: Create Super Administrator Account</small>
        </div>
    </div>

    <div class="installer-body">
        <div class="step-progress">
            <div class="step-item">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                Requirements
            </div>
            <div class="step-item">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                Database
            </div>
            <div class="step-item">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                Migrations
            </div>
            <div class="step-item active">
                <div class="step-circle">4</div>
                Admin & Demo
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 text-sm">
                <i class="fas fa-triangle-exclamation mr-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('installer.admin.process') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Administrator Full Name <span class="text-danger">*</span></label>
                <input type="text" name="admin_name" class="form-control" value="{{ old('admin_name', 'Super Administrator') }}" required>
            </div>

            <div class="form-group">
                <label>Administrator Email <span class="text-danger">*</span></label>
                <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email', 'admin@datingapp.com') }}" required>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Password <span class="text-danger">*</span></label>
                    <input type="password" name="admin_password" class="form-control" placeholder="Min 8 characters" value="Admin@123456" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="admin_password_confirmation" class="form-control" placeholder="Repeat password" value="Admin@123456" required>
                </div>
            </div>

            <div class="card bg-light border p-3 my-3">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="generate_demo" name="generate_demo" value="1" checked>
                    <label class="custom-control-label font-weight-bold" for="generate_demo">
                        Generate ~2,020 Indian Demo Profiles (Male & Female)
                    </label>
                    <small class="form-text text-muted">
                        Automatically populates realistic Indian names, bios, photos, interests, and coordinates across New Delhi, Mumbai, Bengaluru, Hyderabad, Kolkata, etc. These profiles are flagged with <code>is_demo = 1</code> and can be purged anytime from the Admin Panel before production!
                    </small>
                </div>
            </div>

            <button type="submit" class="btn btn-danger btn-block font-weight-bold py-2 mt-4" style="background:#e11d48; border-color:#e11d48;">
                <i class="fas fa-check-circle mr-2"></i> Complete Installation & Launch Admin Panel
            </button>
        </form>
    </div>
</div>

</body>
</html>
