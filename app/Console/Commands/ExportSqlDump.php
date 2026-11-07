<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ExportSqlDump extends Command
{
    protected $signature = 'app:export-sql {--file=database/dating_app.sql}';
    protected $description = 'Export standard MySQL schema and seed data into a portable SQL dump';

    public function handle(): int
    {
        $filePath = base_path($this->option('file'));
        $this->info("Exporting SQL dump to {$filePath}...");

        $sql = "-- ========================================================\n";
        $sql .= "-- Dating Platform Complete MySQL Database Dump\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Target Engine: MySQL 5.7+ / 8.0+ / MariaDB (cPanel Compatible)\n";
        $sql .= "-- ========================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $sql .= "SET time_zone = '+00:00';\n";
        $sql .= "SET NAMES utf8mb4;\n\n";

        // Export table schemas and records
        $tables = [
            'settings',
            'admins',
            'subscription_plans',
            'star_packages',
            'interests',
            'users',
            'profiles',
            'user_locations',
            'user_preferences',
            'photos',
            'interest_user',
            'swipes',
            'matches',
            'star_wallets',
            'star_transactions',
            'payments',
            'payment_refunds',
            'user_subscriptions',
            'user_reports',
            'user_blocks',
            'notifications',
            'otps',
            'activity_logs',
            'personal_access_tokens',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Table structure for `{$table}`\n";
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

            // Generate table schema
            $columns = DB::select("PRAGMA table_info('{$table}')");
            $colDefs = [];
            $pkCol = null;

            foreach ($columns as $col) {
                $type = strtoupper($col->type);
                if (str_contains($type, 'INT')) {
                    $mysqlType = 'BIGINT UNSIGNED';
                } elseif (str_contains($type, 'VARCHAR') || str_contains($type, 'TEXT')) {
                    $mysqlType = ($col->name === 'id') ? 'BIGINT UNSIGNED' : 'VARCHAR(255)';
                    if (str_contains($type, 'TEXT') || in_array($col->name, ['bio', 'details', 'value', 'admin_notes', 'reason', 'error_description', 'user_agent', 'payload'])) {
                        $mysqlType = 'TEXT';
                    }
                } elseif (str_contains($type, 'NUMERIC') || str_contains($type, 'DECIMAL') || str_contains($type, 'FLOAT')) {
                    $mysqlType = ($col->name === 'latitude' || $col->name === 'longitude') ? 'DECIMAL(10, 7)' : 'DECIMAL(10, 2)';
                } elseif (str_contains($type, 'DATETIME') || str_contains($type, 'TIMESTAMP')) {
                    $mysqlType = 'TIMESTAMP NULL DEFAULT NULL';
                } elseif (str_contains($type, 'DATE')) {
                    $mysqlType = 'DATE NULL DEFAULT NULL';
                } else {
                    $mysqlType = 'VARCHAR(255)';
                }

                if ($col->pk == 1) {
                    $colDefs[] = "  `{$col->name}` {$mysqlType} NOT NULL AUTO_INCREMENT";
                    $pkCol = $col->name;
                } else {
                    $nullable = ($col->notnull == 1) ? 'NOT NULL' : 'NULL DEFAULT NULL';
                    $default = ($col->dflt_value !== null) ? "DEFAULT " . $col->dflt_value : "";
                    $colDefs[] = "  `{$col->name}` {$mysqlType} {$nullable} {$default}";
                }
            }

            if ($pkCol) {
                $colDefs[] = "  PRIMARY KEY (`{$pkCol}`)";
            }

            $sql .= "CREATE TABLE `{$table}` (\n" . implode(",\n", $colDefs) . "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

            // Export Rows
            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Dumping data for table `{$table}`\n";
                foreach ($rows->chunk(100) as $chunk) {
                    $insertSql = "INSERT INTO `{$table}` VALUES \n";
                    $valueLines = [];
                    foreach ($chunk as $row) {
                        $values = [];
                        foreach ((array) $row as $val) {
                            if ($val === null) {
                                $values[] = "NULL";
                            } elseif (is_numeric($val)) {
                                $values[] = $val;
                            } else {
                                $values[] = "'" . addslashes((string) $val) . "'";
                            }
                        }
                        $valueLines[] = "(" . implode(", ", $values) . ")";
                    }
                    $sql .= $insertSql . implode(",\n", $valueLines) . ";\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        File::put($filePath, $sql);
        $this->info("Successfully generated SQL dump at: {$filePath} (" . number_format(filesize($filePath)) . " bytes)");

        return 0;
    }
}
