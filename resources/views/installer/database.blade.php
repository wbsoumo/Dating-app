<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Configuration | Dating Platform Installer</title>
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
            <h3 class="mb-0 font-weight-bold" style="font-size: 20px;">Database Connection Setup</h3>
            <small style="color: #94a3b8;">Step 2 of 4: Configure Database Engine</small>
        </div>
    </div>

    <div class="installer-body">
        <div class="step-progress">
            <div class="step-item">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                Requirements
            </div>
            <div class="step-item active">
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

        @if($errors->any())
            <div class="alert alert-danger py-2 text-sm">
                <i class="fas fa-triangle-exclamation mr-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('installer.database.process') }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label class="font-weight-bold">Database Engine</label>
                <select name="db_connection" id="db_connection" class="form-control" onchange="toggleDbFields(this.value)">
                    <option value="mysql" selected>MySQL (Recommended for cPanel Shared Hosting & Production)</option>
                    <option value="sqlite">SQLite (Local Development / Testing)</option>
                </select>
            </div>

            <div id="mysql_fields">
                <div class="row">
                    <div class="col-md-8 form-group">
                        <label>Database Host <span class="text-danger">*</span></label>
                        <input type="text" name="db_host" class="form-control" value="{{ old('db_host', '127.0.0.1') }}" required>
                        <small class="text-muted">Usually <code>127.0.0.1</code> or <code>localhost</code> on cPanel.</small>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Database Port <span class="text-danger">*</span></label>
                        <input type="number" name="db_port" class="form-control" value="{{ old('db_port', 3306) }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Database Name <span class="text-danger">*</span></label>
                    <input type="text" name="db_database" class="form-control" placeholder="e.g. cpaneluser_datingapp" value="{{ old('db_database', 'dating_app') }}" required>
                    <small class="text-muted">Create this database inside cPanel &gt; MySQL Databases wizard.</small>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Database Username <span class="text-danger">*</span></label>
                        <input type="text" name="db_username" class="form-control" placeholder="e.g. cpaneluser_dbuser" value="{{ old('db_username', 'root') }}" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Database Password</label>
                        <input type="password" name="db_password" class="form-control" placeholder="Database user password">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ route('installer.index') }}" class="btn btn-default text-muted">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
                <button type="submit" class="btn btn-danger font-weight-bold px-4" style="background:#e11d48; border-color:#e11d48;">
                    Test Connection & Save <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleDbFields(val) {
        document.getElementById('mysql_fields').style.display = (val === 'mysql') ? 'block' : 'none';
    }
</script>
</body>
</html>
