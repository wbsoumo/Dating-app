<?php
/**
 * DilSe Match Dating Platform - Database Table Checker & Auto-Importer
 *
 * Direct URL: https://taskbazi.site/check_tables.php
 * Import URL: https://taskbazi.site/check_tables.php?import=1
 */

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

@ini_set('memory_limit', '512M');
@set_time_limit(300);

$rootDir = dirname(__DIR__);

// 1. Parse Database Credentials from .env
$envFile = $rootDir . '/.env';
$dbHost = 'localhost';
$dbPort = '3306';
$dbName = 'helnovexaa_dating';
$dbUser = 'helnovexaa_dating';
$dbPass = 'Soumojit1234@';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strncmp($line, '#', 1) === 0) continue;
        if (strncmp($line, 'DB_HOST=', 8) === 0) $dbHost = trim(substr($line, 8), " \"'");
        if (strncmp($line, 'DB_PORT=', 8) === 0) $dbPort = trim(substr($line, 8), " \"'");
        if (strncmp($line, 'DB_DATABASE=', 12) === 0) $dbName = trim(substr($line, 12), " \"'");
        if (strncmp($line, 'DB_USERNAME=', 12) === 0) $dbUser = trim(substr($line, 12), " \"'");
        if (strncmp($line, 'DB_PASSWORD=', 12) === 0) $dbPass = trim(substr($line, 12), " \"'");
    }
}

// 2. Connect to MySQL via PDO
$dbError = null;
$pdo = null;
try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    $dbError = $e->getMessage();
}

// 3. Expected Application Tables (24 core tables)
$requiredTables = [
    'admins'                  => 'Administrator accounts with role management',
    'users'                   => 'Registered dating platform user accounts',
    'profiles'                => 'User biographies, heights, education, dating intent',
    'user_locations'          => 'Real-time GPS coordinates, cities, and distance indexes',
    'photos'                  => 'Profile gallery photos, order sequence, verification status',
    'interests'               => 'Categorized lifestyle & hobby interest tags',
    'interest_user'           => 'User selected interests pivot mappings',
    'user_preferences'        => 'Match discovery filters (age, distance, gender)',
    'swipes'                  => 'User swipe actions (like, pass, superlike)',
    'matches'                 => 'Mutual match conversations and timestamps',
    'subscription_plans'      => 'Silver, Gold, VIP tier pricing and benefits',
    'user_subscriptions'      => 'Active and expired user plan memberships',
    'star_packages'           => 'Star coin top-up purchase bundles',
    'star_wallets'            => 'User coin balances (paid + bonus stars)',
    'star_transactions'       => 'Star ledger credits and feature debit history',
    'payments'                => 'Razorpay orders, transactions, payment methods',
    'payment_refunds'         => 'Razorpay refund records and audit trails',
    'user_reports'            => 'Safety reports with screenshot evidence',
    'user_blocks'             => 'User block lists and safety filters',
    'notifications'           => 'Push and in-app user activity notifications',
    'otps'                    => 'Mobile & email one-time password records',
    'settings'                => 'Application, Razorpay, and matching configuration',
    'activity_logs'           => 'Administrator audit trail and action logs',
    'personal_access_tokens'  => 'Laravel Sanctum REST API bearer tokens',
];

$importLog = [];
$importedCount = 0;
$executedStatements = 0;

// 4. Handle Import Action
if ($pdo && (isset($_GET['import']) || isset($_POST['import']))) {
    $sqlFile = $rootDir . '/database/dating_app.sql';
    if (!file_exists($sqlFile)) {
        $importLog[] = "[Error] database/dating_app.sql not found at: {$sqlFile}";
    } else {
        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
            $pdo->exec("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';");
            $pdo->exec("SET NAMES utf8mb4;");

            $handle = fopen($sqlFile, 'r');
            $currentStmt = '';

            while (($line = fgets($handle)) !== false) {
                $trimmed = trim($line);
                if (empty($trimmed) || strncmp($trimmed, '--', 2) === 0 || strncmp($trimmed, '/*', 2) === 0) {
                    continue;
                }

                $currentStmt .= $line;

                if (substr($trimmed, -1) === ';') {
                    try {
                        $pdo->exec($currentStmt);
                        $executedStatements++;
                    } catch (PDOException $ex) {
                        // Ignore harmless duplicate insert warnings or log
                    }
                    $currentStmt = '';
                }
            }
            fclose($handle);

            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
            $importLog[] = "[Success] Executed {$executedStatements} SQL statements successfully!";
        } catch (Throwable $e) {
            $importLog[] = "[Error] SQL import interrupted: " . $e->getMessage();
        }
    }
}

// 5. Query Existing Tables and Count Rows
$existingTables = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tableName = $row[0];
            $count = 0;
            try {
                $countStmt = $pdo->query("SELECT count(*) as c FROM `{$tableName}`");
                $count = (int) $countStmt->fetchColumn();
            } catch (Throwable $t) {
                $count = -1;
            }
            $existingTables[$tableName] = $count;
        }
    } catch (Throwable $e) {
        $dbError = $e->getMessage();
    }
}

$missingCount = 0;
foreach ($requiredTables as $tName => $desc) {
    if (!isset($existingTables[$tName])) {
        $missingCount++;
    }
}

$allGood = ($missingCount === 0 && empty($dbError));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Health & Table Importer | DilSe Match</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b0f19;
            --card-bg: #151c2c;
            --card-border: #232f45;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #f43f5e;
            --primary-hover: #e11d48;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }
        * { box-sizing: border-box; }
        body {
            background: var(--bg);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 30px 20px;
            display: flex;
            justify-content: center;
        }
        .container {
            max-width: 980px;
            width: 100%;
        }
        .header {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .header-status {
            font-size: 14px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .status-ready { background: rgba(16, 185, 129, 0.15); color: var(--success); border: 1px solid var(--success); }
        .status-missing { background: rgba(239, 68, 68, 0.15); color: var(--danger); border: 1px solid var(--danger); }
        
        .action-banner {
            background: linear-gradient(135deg, rgba(244,63,94,0.1), rgba(30,41,59,0.7));
            border: 1px solid var(--primary);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .action-banner p { margin: 0; font-size: 15px; color: #cbd5e1; line-height: 1.5; }
        .action-banner strong { color: #fff; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 22px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 14px rgba(244,63,94,0.4);
        }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-success {
            background: var(--success);
            color: #fff;
            box-shadow: 0 4px 14px rgba(16,185,129,0.4);
        }
        .btn-success:hover { background: #059669; }

        .log-box {
            background: #050811;
            border: 1px solid #1e293b;
            border-radius: 8px;
            padding: 16px;
            font-family: 'Fira Code', monospace;
            font-size: 13px;
            color: #38bdf8;
            margin-bottom: 24px;
            max-height: 180px;
            overflow-y: auto;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 14px;
            margin-bottom: 30px;
        }
        .table-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            transition: transform 0.15s ease, border-color 0.15s ease;
        }
        .table-card:hover {
            transform: translateY(-2px);
            border-color: #38bdf8;
        }
        .table-name {
            font-family: 'Fira Code', monospace;
            font-size: 14px;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 4px;
        }
        .table-desc {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.4;
        }
        .badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            white-space: nowrap;
        }
        .badge-ok { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .badge-err { background: rgba(239, 68, 68, 0.2); color: #f87171; }

        .footer-nav {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding-top: 10px;
        }
        .footer-nav a {
            color: #38bdf8;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        .footer-nav a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>🗄️ Database Table Health Inspector</h1>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                    Database: <code><?= htmlspecialchars($dbName) ?></code> (User: <code><?= htmlspecialchars($dbUser) ?></code> @ <code><?= htmlspecialchars($dbHost) ?></code>)
                </div>
            </div>
            <div>
                <?php if ($allGood): ?>
                    <span class="header-status status-ready">✅ All 24 Tables Ready</span>
                <?php else: ?>
                    <span class="header-status status-missing">⚠️ <?= $missingCount ?> Tables Missing</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($importLog)): ?>
            <div class="log-box">
                <?php foreach ($importLog as $logLine): ?>
                    <div><?= htmlspecialchars($logLine) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($dbError): ?>
            <div style="background: rgba(239,68,68,0.15); border: 1px solid var(--danger); padding: 18px; border-radius: 10px; margin-bottom: 24px; color: #fca5a5;">
                <strong>Database Connection Error:</strong><br><?= htmlspecialchars($dbError) ?>
            </div>
        <?php endif; ?>

        <?php if ($missingCount > 0): ?>
            <div class="action-banner">
                <div>
                    <strong>Some tables are not yet created in your database.</strong>
                    <p>Click the button below to import the complete schema and seed initial data into <code><?= htmlspecialchars($dbName) ?></code> instantly.</p>
                </div>
                <div>
                    <a href="?import=1" class="btn btn-primary">⚡ Import All Missing Tables Now</a>
                </div>
            </div>
        <?php else: ?>
            <div class="action-banner" style="background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(30,41,59,0.7)); border-color: var(--success);">
                <div>
                    <strong style="color: #6ee7b7; font-size: 16px;">🎉 Database Schema is 100% Complete & Synchronized!</strong>
                    <p>All tables, settings, plans, coin packages, and admin credentials are ready to handle live requests.</p>
                </div>
                <div>
                    <a href="/admin/dashboard" class="btn btn-success">🚀 Go to Admin Dashboard &rarr;</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid">
            <?php foreach ($requiredTables as $tableName => $description): ?>
                <?php 
                    $exists = isset($existingTables[$tableName]);
                    $rows = $exists ? $existingTables[$tableName] : 0;
                ?>
                <div class="table-card" style="border-left: 4px solid <?= $exists ? 'var(--success)' : 'var(--danger)' ?>;">
                    <div style="flex: 1; padding-right: 12px;">
                        <div class="table-name"><?= htmlspecialchars($tableName) ?></div>
                        <div class="table-desc"><?= htmlspecialchars($description) ?></div>
                    </div>
                    <div>
                        <?php if ($exists): ?>
                            <span class="badge badge-ok">✅ <?= number_format($rows) ?> rows</span>
                        <?php else: ?>
                            <span class="badge badge-err">❌ Missing</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="footer-nav">
            <a href="/admin/login">Admin Login</a>
            <span>&bull;</span>
            <a href="/admin/dashboard">Admin Dashboard</a>
            <span>&bull;</span>
            <a href="/api/docs">API Documentation</a>
            <span>&bull;</span>
            <a href="?import=1" onclick="return confirm('Do you want to re-run the schema import?');">Re-run Schema Import</a>
        </div>
    </div>
</body>
</html>
