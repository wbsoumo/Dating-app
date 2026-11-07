<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Wizard | Dating Platform Backend</title>
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
        .badge-passed { background: #dcfce7; color: #166534; font-weight: 600; padding: 4px 8px; border-radius: 4px; }
        .badge-failed { background: #fee2e2; color: #991b1b; font-weight: 600; padding: 4px 8px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="installer-card">
    <div class="installer-header">
        <i class="fa-solid fa-heart-pulse"></i>
        <div>
            <h3 class="mb-0 font-weight-bold" style="font-size: 20px;">Dating Platform Automated Installer</h3>
            <small style="color: #94a3b8;">cPanel Shared Hosting & VPS Setup Wizard</small>
        </div>
    </div>

    <div class="installer-body">
        <div class="step-progress">
            <div class="step-item active">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                Requirements
            </div>
            <div class="step-item">
                <div class="step-circle">2</div>
                Database
            </div>
            <div class="step-item">
                <div class="step-circle">3</div>
                Migrations
            </div>
            <div class="step-item">
                <div class="step-circle">4</div>
                Admin & Demo
            </div>
        </div>

        <h5 class="font-weight-bold mb-3">System Environment & PHP Extensions</h5>
        <table class="table table-sm table-bordered mb-4">
            <tbody>
                @foreach($requirements as $label => $req)
                    <tr>
                        <td class="font-weight-bold">{{ $label }}</td>
                        <td>{{ $req['current'] }}</td>
                        <td class="text-right">
                            @if($req['passed'])
                                <span class="badge-passed"><i class="fas fa-check mr-1"></i> Passed</span>
                            @else
                                <span class="badge-failed"><i class="fas fa-times mr-1"></i> Missing</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h5 class="font-weight-bold mb-3">Directory Write Permissions</h5>
        <table class="table table-sm table-bordered mb-4">
            <tbody>
                @foreach($directories as $label => $dir)
                    <tr>
                        <td class="font-weight-bold"><code>{{ $label }}</code></td>
                        <td>{{ $dir['status'] }}</td>
                        <td class="text-right">
                            @if($dir['passed'])
                                <span class="badge-passed"><i class="fas fa-check mr-1"></i> Writable</span>
                            @else
                                <span class="badge-failed"><i class="fas fa-times mr-1"></i> Not Writable</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <span class="text-muted text-sm">
                @if($allPassed)
                    <i class="fas fa-circle-check text-success mr-1"></i> All server checks passed!
                @else
                    <i class="fas fa-circle-exclamation text-danger mr-1"></i> Please resolve missing requirements or file permissions.
                @endif
            </span>

            @if($allPassed)
                <a href="{{ route('installer.database') }}" class="btn btn-danger font-weight-bold px-4" style="background:#e11d48; border-color:#e11d48;">
                    Continue to Database Setup <i class="fas fa-arrow-right ml-1"></i>
                </a>
            @else
                <button class="btn btn-secondary" disabled>Fix Requirements to Continue</button>
            @endif
        </div>
    </div>
</div>

</body>
</html>
