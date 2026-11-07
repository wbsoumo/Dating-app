<?php
/**
 * DilSe Match Dating Platform - Web Deployment & Code Puller
 *
 * Trigger URL: https://taskbazi.site/deploy.php?key=taskbazi_dating_deploy_2026
 * Also supports GitHub Webhook POST requests with the secret key in URL or header.
 */

// Secret deployment key (Must match URL ?key= parameter)
$secretKey = 'taskbazi_dating_deploy_2026';

// Check if custom key is defined in .env
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        if (str_starts_with(trim($line), 'DEPLOY_SECRET_KEY=')) {
            $parts = explode('=', trim($line), 2);
            if (!empty($parts[1])) {
                $secretKey = trim($parts[1], " \t\n\r\0\x0B\"'");
            }
            break;
        }
    }
}

$providedKey = $_GET['key'] ?? $_POST['key'] ?? ($_SERVER['HTTP_X_DEPLOY_KEY'] ?? '');

if (empty($providedKey) || !hash_equals($secretKey, $providedKey)) {
    http_response_code(403);
    header('Content-Type: text/plain');
    echo "403 Forbidden: Invalid or missing deployment secret key.\n";
    echo "Usage: https://taskbazi.site/deploy.php?key=YOUR_DEPLOY_SECRET_KEY\n";
    exit;
}

$rootDir = dirname(__DIR__);
chdir($rootDir);

$startTime = microtime(true);
$output = [];
$output[] = "========================================================";
$output[] = " DilSe Match Dating Platform - Web Pull & Deployment";
$output[] = " Started: " . date('Y-m-d H:i:s');
$output[] = " Target Directory: " . $rootDir;
$output[] = "========================================================\n";

// Function to execute and capture
function runCmd($cmd, &$output) {
    $output[] = "$ {$cmd}";
    $descriptors = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($cmd, $descriptors, $pipes);
    if (is_resource($process)) {
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        if (!empty($stdout)) {
            $output[] = trim($stdout);
        }
        if (!empty($stderr)) {
            $output[] = "[stderr] " . trim($stderr);
        }
    }
    $output[] = "";
}

// 1. Pull latest git code (auto-detect active branch: main or master)
$targetBranch = $_GET['branch'] ?? $_POST['branch'] ?? '';
if (empty($targetBranch) || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $targetBranch)) {
    $branchDetect = [];
    @exec('git rev-parse --abbrev-ref HEAD 2>/dev/null', $branchDetect);
    $targetBranch = (!empty($branchDetect[0]) && $branchDetect[0] !== 'HEAD') ? trim($branchDetect[0]) : 'main';
}
runCmd("git fetch origin {$targetBranch} 2>&1", $output);
runCmd("git reset --hard origin/{$targetBranch} 2>&1", $output);

// 2. Run migrations
runCmd('php artisan migrate --force 2>&1', $output);

// 3. Clear and optimize caches
runCmd('php artisan optimize:clear 2>&1', $output);
runCmd('php artisan config:cache 2>&1', $output);
runCmd('php artisan route:cache 2>&1', $output);
runCmd('php artisan view:cache 2>&1', $output);

// 4. Permissions check
runCmd('chmod -R 775 storage bootstrap/cache public/uploads 2>&1', $output);

$duration = round(microtime(true) - $startTime, 2);
$output[] = "========================================================";
$output[] = " Deployment completed successfully in {$duration}s!";
$output[] = " Platform URL: https://taskbazi.site";
$output[] = " Admin Panel: https://taskbazi.site/admin/login";
$output[] = " API Docs:    https://taskbazi.site/api/docs";
$output[] = "========================================================";

// Return JSON if requested
if (isset($_GET['format']) && $_GET['format'] === 'json' || isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'duration_seconds' => $duration,
        'timestamp' => date('c'),
        'log' => $output,
    ], JSON_PRETTY_PRINT);
    exit;
}

// Render clean terminal UI
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployment Output | DilSe Match Dating Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #090d16;
            color: #f8fafc;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 30px 20px;
            display: flex;
            justify-content: center;
        }
        .container {
            max-width: 860px;
            width: 100%;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 20px;
            font-weight: 700;
        }
        .badge {
            background: #10b981;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }
        pre {
            background: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 8px;
            padding: 24px;
            font-family: 'Fira Code', monospace;
            font-size: 13px;
            line-height: 1.6;
            color: #38bdf8;
            overflow-x: auto;
            white-space: pre-wrap;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5);
        }
        .links {
            margin-top: 20px;
            display: flex;
            gap: 15px;
        }
        .links a {
            color: #f43f5e;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">🚀 DilSe Match Web Puller</div>
            <div class="badge">Deployment Finished (<?= $duration ?>s)</div>
        </div>
        <pre><?= htmlspecialchars(implode("\n", $output)) ?></pre>
        <div class="links">
            <a href="/admin/dashboard" target="_blank">Go to Admin Dashboard &rarr;</a>
            <a href="/api/docs" target="_blank">View API Documentation &rarr;</a>
        </div>
    </div>
</body>
</html>
