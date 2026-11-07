<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

$phpVersion = PHP_VERSION;
$rootDir = dirname(__DIR__);
$vendorAutoload = $rootDir . '/vendor/autoload.php';
$envFile = $rootDir . '/.env';
$storageDir = $rootDir . '/storage';
$installedLock = $rootDir . '/storage/installed.lock';

$hasVendor = file_exists($vendorAutoload);
$hasEnv = file_exists($envFile);
$storageWritable = is_writable($storageDir);
$disabledFunctions = explode(',', ini_get('disable_functions'));
$disabledFunctions = array_map('trim', $disabledFunctions);

$phpOk = version_compare($phpVersion, '8.2.0', '>=');

?>
<!DOCTYPE html>
<html>
<head>
    <title>taskbazi.site System Diagnostics</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 40px 20px; }
        .box { max-width: 700px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { margin-top: 0; font-size: 24px; color: #38bdf8; }
        .item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #334155; font-size: 15px; }
        .label { font-weight: 600; color: #94a3b8; }
        .status-ok { color: #10b981; font-weight: 700; }
        .status-err { color: #ef4444; font-weight: 700; }
        .status-warn { color: #f59e0b; font-weight: 700; }
        .alert { background: #450a0a; border: 1px solid #dc2626; color: #fecaca; padding: 15px; border-radius: 8px; margin-top: 20px; line-height: 1.5; font-size: 14px; }
        .success { background: #064e3b; border: 1px solid #059669; color: #a7f3d0; padding: 15px; border-radius: 8px; margin-top: 20px; line-height: 1.5; font-size: 14px; }
        code { background: #0f172a; padding: 3px 6px; border-radius: 4px; font-family: monospace; color: #f43f5e; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🔍 taskbazi.site Server Diagnostics</h1>

        <div class="item">
            <span class="label">PHP Version</span>
            <span class="<?= $phpOk ? 'status-ok' : 'status-err' ?>">
                <?= $phpVersion ?> <?= $phpOk ? '(Supported)' : '(REQUIRES PHP 8.2 or 8.3 in cPanel)' ?>
            </span>
        </div>

        <div class="item">
            <span class="label">Composer Vendor Folder (`vendor/autoload.php`)</span>
            <span class="<?= $hasVendor ? 'status-ok' : 'status-err' ?>">
                <?= $hasVendor ? 'Present' : 'MISSING (Needs composer install or vendor upload)' ?>
            </span>
        </div>

        <div class="item">
            <span class="label">Environment File (`.env`)</span>
            <span class="<?= $hasEnv ? 'status-ok' : 'status-err' ?>">
                <?= $hasEnv ? 'Present' : 'Missing' ?>
            </span>
        </div>

        <div class="item">
            <span class="label">Storage Folder Writable</span>
            <span class="<?= $storageWritable ? 'status-ok' : 'status-err' ?>">
                <?= $storageWritable ? 'Writable (775)' : 'Not Writable' ?>
            </span>
        </div>

        <div class="item">
            <span class="label">Disabled Functions</span>
            <span class="status-warn" style="font-size: 12px; max-width: 350px; text-align: right;">
                <?= !empty(array_filter($disabledFunctions)) ? htmlspecialchars(implode(', ', array_slice($disabledFunctions, 0, 8))) : 'None' ?>
            </span>
        </div>

        <?php if (!$phpOk): ?>
            <div class="alert">
                <strong>CRITICAL: PHP Version Incompatible!</strong><br>
                Laravel 11 requires <strong>PHP 8.2</strong> or <strong>PHP 8.3</strong>.<br>
                Your cPanel is currently using <strong>PHP <?= $phpVersion ?></strong> for this domain.<br><br>
                <strong>How to fix:</strong> In cPanel, go to <strong>MultiPHP Manager</strong>, select domain <code>taskbazi.site</code>, and change PHP version to <strong>PHP 8.2</strong> or <strong>PHP 8.3</strong>.
            </div>
        <?php endif; ?>

        <?php if (!$hasVendor): ?>
            <div class="alert">
                <strong>CRITICAL: Composer Dependencies Missing!</strong><br>
                The <code>vendor/</code> directory is missing on the server. Laravel cannot run without its vendor dependencies.
            </div>
        <?php endif; ?>

        <?php if ($phpOk && $hasVendor && $hasEnv): ?>
            <div class="success">
                <strong>All Core Requirements Met!</strong><br>
                You can access the admin panel at: <a href="/admin/login" style="color:#38bdf8;">/admin/login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
