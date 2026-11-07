<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoActivitySeeder;
use Database\Seeders\DemoUserSeeder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class InstallerController extends Controller
{
    protected string $lockFile;

    public function __construct()
    {
        $this->lockFile = storage_path('installed.lock');
    }

    public function index()
    {
        if (File::exists($this->lockFile)) {
            return view('installer.already_installed');
        }

        $requirements = $this->checkRequirements();
        $directories = $this->checkPermissions();
        $allPassed = !in_array(false, array_column($requirements, 'passed')) &&
                     !in_array(false, array_column($directories, 'passed'));

        return view('installer.welcome', compact('requirements', 'directories', 'allPassed'));
    }

    public function showDatabaseForm()
    {
        if (File::exists($this->lockFile)) {
            return redirect()->route('installer.index');
        }

        return view('installer.database');
    }

    public function processDatabase(Request $request)
    {
        if (File::exists($this->lockFile)) {
            return redirect()->route('installer.index');
        }

        $connection = $request->input('db_connection', 'sqlite');

        if ($connection === 'mysql') {
            $request->validate([
                'db_host' => 'required|string',
                'db_port' => 'required|integer',
                'db_database' => 'required|string',
                'db_username' => 'required|string',
                'db_password' => 'nullable|string',
            ]);

            // Test connection
            try {
                $dsn = "mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_database}";
                $pdo = new \PDO($dsn, $request->db_username, $request->db_password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            } catch (\Throwable $e) {
                return back()->withErrors(['db_database' => 'Database connection failed: ' . $e->getMessage()])->withInput();
            }

            // Update .env
            $this->updateEnv([
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $request->db_database,
                'DB_USERNAME' => $request->db_username,
                'DB_PASSWORD' => $request->db_password ?? '',
            ]);
        }

        return redirect()->route('installer.migrate');
    }

    public function showMigrate()
    {
        if (File::exists($this->lockFile)) {
            return redirect()->route('installer.index');
        }

        return view('installer.migrate');
    }

    public function processMigrate(Request $request)
    {
        if (File::exists($this->lockFile)) {
            return redirect()->route('installer.index');
        }

        try {
            // Run migrations and master seeders
            Artisan::call('migrate:fresh', ['--force' => true]);
            $seeder = new DatabaseSeeder();
            $seeder->run();

            return redirect()->route('installer.admin');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Migration failed: ' . $e->getMessage()]);
        }
    }

    public function showAdmin()
    {
        if (File::exists($this->lockFile)) {
            return redirect()->route('installer.index');
        }

        return view('installer.admin');
    }

    public function processAdmin(Request $request)
    {
        if (File::exists($this->lockFile)) {
            return redirect()->route('installer.index');
        }

        $request->validate([
            'admin_name' => 'required|string|max:100',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8|confirmed',
            'generate_demo' => 'nullable|boolean',
        ]);

        try {
            // Create Super Admin
            Admin::updateOrCreate(
                ['email' => $request->admin_email],
                [
                    'name' => $request->admin_name,
                    'password' => Hash::make($request->admin_password),
                    'role' => 'super_admin',
                    'status' => 'active',
                ]
            );

            // Optionally generate demo profiles
            if ($request->boolean('generate_demo')) {
                $demoSeeder = new DemoUserSeeder();
                $demoSeeder->run(2020);

                $activitySeeder = new DemoActivitySeeder();
                $activitySeeder->run();
            }

            // Lock installer permanently
            File::put($this->lockFile, json_encode([
                'installed_at' => date('Y-m-d H:i:s'),
                'admin_email' => $request->admin_email,
            ]));

            return redirect()->route('admin.login')
                ->with('success', 'Installation completed successfully! You can now log in with your administrator credentials.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Setup error: ' . $e->getMessage()])->withInput();
        }
    }

    protected function checkRequirements(): array
    {
        return [
            'PHP Version (>= 8.2)' => [
                'current' => phpversion(),
                'passed' => version_compare(phpversion(), '8.2.0', '>='),
            ],
            'PDO Extension' => [
                'current' => extension_loaded('pdo') ? 'Installed' : 'Missing',
                'passed' => extension_loaded('pdo'),
            ],
            'OpenSSL Extension' => [
                'current' => extension_loaded('openssl') ? 'Installed' : 'Missing',
                'passed' => extension_loaded('openssl'),
            ],
            'Mbstring Extension' => [
                'current' => extension_loaded('mbstring') ? 'Installed' : 'Missing',
                'passed' => extension_loaded('mbstring'),
            ],
            'Fileinfo Extension' => [
                'current' => extension_loaded('fileinfo') ? 'Installed' : 'Missing',
                'passed' => extension_loaded('fileinfo'),
            ],
            'JSON Extension' => [
                'current' => extension_loaded('json') ? 'Installed' : 'Missing',
                'passed' => extension_loaded('json'),
            ],
            'cURL Extension' => [
                'current' => extension_loaded('curl') ? 'Installed' : 'Missing',
                'passed' => extension_loaded('curl'),
            ],
        ];
    }

    protected function checkPermissions(): array
    {
        $directories = [
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
            'public/uploads' => public_path('uploads'),
        ];

        $results = [];
        foreach ($directories as $label => $path) {
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0755, true, true);
            }
            $isWritable = is_writable($path);
            $results[$label] = [
                'path' => $path,
                'passed' => $isWritable,
                'status' => $isWritable ? 'Writable (0755)' : 'Not Writable',
            ];
        }

        return $results;
    }

    protected function updateEnv(array $data): void
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            File::copy(base_path('.env.example'), $envPath);
        }

        $envContent = File::get($envPath);

        foreach ($data as $key => $value) {
            if (str_contains($value, ' ') && !str_starts_with($value, '"')) {
                $value = '"' . $value . '"';
            }

            if (preg_match("/^{$key}=/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);
    }
}
