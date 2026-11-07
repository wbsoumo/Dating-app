<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// 1. Check PHP version requirement (Laravel 11 requires PHP >= 8.2)
if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PHP Version Update Required | DilSe Match</title>
        <style>
            body { background: #0f172a; color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 40px 20px; display: flex; justify-content: center; }
            .card { max-width: 650px; background: #1e293b; border-radius: 12px; padding: 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); border: 1px solid #334155; }
            h2 { color: #f43f5e; margin-top: 0; }
            .badge { background: #ef4444; color: #fff; padding: 3px 8px; border-radius: 6px; font-weight: bold; }
            .step { background: #090d16; padding: 18px; border-radius: 8px; margin: 18px 0; border-left: 4px solid #38bdf8; font-size: 14px; line-height: 1.7; }
            code { background: #334155; padding: 2px 6px; border-radius: 4px; color: #38bdf8; font-family: monospace; }
            .btn { display: inline-block; background: #f43f5e; color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 6px; font-weight: 600; margin-top: 10px; }
        </style>
    </head>
    <body>
        <div class="card">
            <h2>⚠️ PHP Version Update Required</h2>
            <p>Your cPanel domain is currently running <span class="badge">PHP <?= htmlspecialchars(PHP_VERSION) ?></span>.<br>Laravel 11 requires <strong>PHP 8.2</strong> or <strong>PHP 8.3</strong>.</p>
            
            <div class="step">
                <strong>How to switch in cPanel (Takes 30 seconds):</strong><br>
                1. Log in to your cPanel.<br>
                2. Search for and click <strong>MultiPHP Manager</strong>.<br>
                3. Under <em>Set PHP Version per Domain</em>, check the box next to <code>taskbazi.site</code>.<br>
                4. In the top-right <strong>PHP Version</strong> dropdown, select <strong>PHP 8.2</strong> (or <strong>PHP 8.3</strong>).<br>
                5. Click <strong>Apply</strong> and reload this page.
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 2. Check if Composer vendor directory exists
if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Composer Dependencies Setup | DilSe Match</title>
        <style>
            body { background: #0f172a; color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 40px 20px; display: flex; justify-content: center; }
            .card { max-width: 650px; background: #1e293b; border-radius: 12px; padding: 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); border: 1px solid #334155; }
            h2 { color: #f59e0b; margin-top: 0; }
            .step { background: #090d16; padding: 18px; border-radius: 8px; margin: 18px 0; border-left: 4px solid #10b981; font-size: 14px; line-height: 1.7; }
            code { background: #334155; padding: 2px 6px; border-radius: 4px; color: #38bdf8; font-family: monospace; }
        </style>
    </head>
    <body>
        <div class="card">
            <h2>📦 Composer Dependencies Not Installed</h2>
            <p>The <code>vendor/</code> directory is missing on the server. Laravel requires its dependencies to run.</p>
            
            <div class="step">
                <strong>Option A — Via cPanel Terminal or SSH:</strong><br>
                <code>cd ~/public_html && composer install --no-dev --optimize-autoloader</code>
            </div>

            <div class="step">
                <strong>Option B — Via Web Puller:</strong><br>
                Visit <a href="/deploy.php?key=taskbazi_dating_deploy_2026" style="color:#38bdf8;">/deploy.php?key=taskbazi_dating_deploy_2026</a> to trigger the automated deployment script.
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
