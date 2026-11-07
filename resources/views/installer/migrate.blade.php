<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration & Seeders | Dating Platform Installer</title>
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
            <h3 class="mb-0 font-weight-bold" style="font-size: 20px;">Database Schema Creation</h3>
            <small style="color: #94a3b8;">Step 3 of 4: Automatic Table Creation & Seeders</small>
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
            <div class="step-item active">
                <div class="step-circle">3</div>
                Migrations
            </div>
            <div class="step-item">
                <div class="step-circle">4</div>
                Admin & Demo
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 text-sm">
                <i class="fas fa-triangle-exclamation mr-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <h5 class="font-weight-bold mb-2">Automated Database Installation</h5>
        <p class="text-muted text-sm mb-3">
            Clicking the button below will automatically create all production MySQL tables, foreign keys, and indexes, and populate initial master data:
        </p>

        <div class="row text-sm mb-4">
            <div class="col-6">
                <ul class="list-unstyled text-muted">
                    <li><i class="fas fa-check-circle text-success mr-1"></i> Users & Authentication</li>
                    <li><i class="fas fa-check-circle text-success mr-1"></i> Profiles & Attributes</li>
                    <li><i class="fas fa-check-circle text-success mr-1"></i> User Locations (Spatial)</li>
                    <li><i class="fas fa-check-circle text-success mr-1"></i> Photos & Thumbnails</li>
                    <li><i class="fas fa-check-circle text-success mr-1"></i> Hobbies & Categories (Seed)</li>
                    <li><i class="fas fa-check-circle text-success mr-1"></i> User Preferences (Hidden)</li>
                </ul>
            </div>
            <div class="col-6">
                <ul class="list-unstyled text-muted">
                    <li><i class="fas fa-check-circle text-success mr-1"></i> Swipes & Mutual Matches</li>
                    <li><i class="fas fa-check-circle text-success mr-1"></i> VIP Subscription Plans (Seed)</li>
                    <li><i class="fas fa-check-circle text-success mr-1"></i> Star Packages & Wallets (Seed)</li>
                    <li><i class="fas fa-check-circle text-success mr-1"></i> Razorpay Payments & Refunds</li>
                    <li><i class="fas fa-check-circle text-success mr-1"></i> User Reports & Safety Blocks</li>
                    <li><i class="fas fa-check-circle text-success mr-1"></i> Global System Settings (Seed)</li>
                </ul>
            </div>
        </div>

        <form action="{{ route('installer.migrate.process') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger btn-block font-weight-bold py-2" style="background:#e11d48; border-color:#e11d48;">
                <i class="fas fa-database mr-2"></i> Run Automated Migrations & Master Seeders
            </button>
        </form>
    </div>
</div>

</body>
</html>
