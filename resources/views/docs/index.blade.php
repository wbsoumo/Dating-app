<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }} - REST API Documentation (v1)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --accent: #f43f5e;
            --accent-hover: #e11d48;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --code-bg: #090d16;
            --method-get: #10b981;
            --method-post: #3b82f6;
            --method-put: #f59e0b;
            --method-delete: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar */
        aside {
            width: 300px;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            position: fixed;
            top: 0; bottom: 0; left: 0;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            z-index: 10;
        }
        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-brand i { color: var(--accent); font-size: 26px; }
        .sidebar-brand h1 { font-size: 18px; font-weight: 700; letter-spacing: -0.3px; }
        .sidebar-search { padding: 16px 20px; border-bottom: 1px solid var(--border-color); }
        .search-box {
            position: relative;
        }
        .search-box input {
            width: 100%;
            padding: 8px 12px 8px 34px;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-main);
            font-size: 13px;
        }
        .search-box i { position: absolute; left: 12px; top: 11px; color: var(--text-muted); font-size: 13px; }
        .sidebar-nav { padding: 16px 0; flex: 1; }
        .nav-category {
            padding: 10px 20px 4px 20px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            font-weight: 600;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 20px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            transition: all 0.15s ease;
        }
        .nav-item:hover { background: var(--bg-tertiary); color: var(--text-main); }
        .badge-method {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            font-family: 'Fira Code', monospace;
        }
        .badge-GET { background: rgba(16, 185, 129, 0.2); color: var(--method-get); }
        .badge-POST { background: rgba(59, 130, 246, 0.2); color: var(--method-post); }
        .badge-PUT { background: rgba(245, 158, 11, 0.2); color: var(--method-put); }
        .badge-DELETE { background: rgba(239, 68, 68, 0.2); color: var(--method-delete); }

        /* Main Content */
        main {
            margin-left: 300px;
            flex: 1;
            padding: 40px 60px;
            max-width: 1100px;
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        .header-bar .btn {
            background: var(--accent);
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }
        .header-bar .btn:hover { background: var(--accent-hover); }
        .btn-outline {
            background: transparent !important;
            border: 1px solid var(--border-color);
            color: var(--text-main) !important;
            margin-right: 10px;
        }
        .btn-outline:hover { background: var(--bg-secondary) !important; }

        .api-meta-box {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 40px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .meta-item h4 { font-size: 12px; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; }
        .meta-item p { font-size: 14px; font-weight: 600; color: var(--text-main); font-family: 'Fira Code', monospace; }

        /* Endpoint Card */
        .endpoint-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        .endpoint-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .endpoint-path {
            font-family: 'Fira Code', monospace;
            font-size: 15px;
            font-weight: 600;
        }
        .endpoint-title {
            color: var(--text-muted);
            font-size: 13px;
            margin-left: auto;
        }
        .auth-badge {
            background: rgba(244, 63, 94, 0.15);
            color: var(--accent);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .endpoint-body {
            padding: 24px;
        }
        .endpoint-desc {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .section-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin: 16px 0 8px 0;
        }
        pre {
            background: var(--code-bg);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 14px 18px;
            font-family: 'Fira Code', monospace;
            font-size: 12.5px;
            color: #38bdf8;
            overflow-x: auto;
            position: relative;
        }
        .copy-btn {
            position: absolute;
            top: 10px; right: 10px;
            background: var(--bg-tertiary);
            color: var(--text-muted);
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
        }
        .copy-btn:hover { color: #fff; background: var(--accent); }
    </style>
</head>
<body>

    <aside>
        <div class="sidebar-brand">
            <i class="fa-solid fa-heart-pulse"></i>
            <div>
                <h1>{{ $appName }}</h1>
                <span style="font-size: 11px; color: var(--text-muted);">REST API v1 Documentation</span>
            </div>
        </div>

        <div class="sidebar-search">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="apiSearch" placeholder="Search API endpoints..." onkeyup="filterEndpoints()">
            </div>
        </div>

        <div class="sidebar-nav">
            @foreach($endpoints as $groupName => $routes)
                <div class="nav-category">{{ $groupName }}</div>
                @foreach($routes as $route)
                    <a href="#{{ Str::slug($route['method'] . '-' . $route['path']) }}" class="nav-item">
                        <span class="badge-method badge-{{ $route['method'] }}">{{ $route['method'] }}</span>
                        <span>{{ $route['title'] }}</span>
                    </a>
                @endforeach
            @endforeach
        </div>
    </aside>

    <main>
        <div class="header-bar">
            <div>
                <h2 style="font-size: 26px; font-weight: 700;">API Reference & Integration Guide</h2>
                <p style="color: var(--text-muted); font-size: 14px; margin-top: 4px;">Fully documented endpoints for mobile iOS & Android dating application integration.</p>
            </div>
            <div>
                <a href="/admin/login" class="btn btn-outline" target="_blank"><i class="fa-solid fa-gauge-high"></i> Admin Panel</a>
                <a href="{{ route('api.docs.postman') }}" class="btn"><i class="fa-solid fa-download"></i> Postman Collection</a>
            </div>
        </div>

        <div class="api-meta-box">
            <div class="meta-item">
                <h4>Base URL (v1)</h4>
                <p>{{ $baseUrl }}</p>
            </div>
            <div class="meta-item">
                <h4>Authentication</h4>
                <p>Bearer &lt;token&gt; (Sanctum)</p>
            </div>
            <div class="meta-item">
                <h4>Response Format</h4>
                <p>application/json</p>
            </div>
            <div class="meta-item">
                <h4>Rate Limiting</h4>
                <p>60 requests / minute</p>
            </div>
        </div>

        @foreach($endpoints as $groupName => $routes)
            <div style="margin-bottom: 40px;" class="category-block" data-category="{{ strtolower($groupName) }}">
                <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-folder-open" style="color: var(--accent); font-size: 18px;"></i>
                    {{ $groupName }}
                </h3>

                @foreach($routes as $route)
                    <div class="endpoint-card" id="{{ Str::slug($route['method'] . '-' . $route['path']) }}" data-search="{{ strtolower($route['title'] . ' ' . $route['path'] . ' ' . $route['method']) }}">
                        <div class="endpoint-header">
                            <span class="badge-method badge-{{ $route['method'] }}">{{ $route['method'] }}</span>
                            <span class="endpoint-path">{{ $route['path'] }}</span>
                            @if($route['auth'])
                                <span class="auth-badge"><i class="fa-solid fa-lock"></i> Bearer Auth</span>
                            @else
                                <span class="auth-badge" style="background: rgba(16, 185, 129, 0.15); color: var(--method-get);"><i class="fa-solid fa-lock-open"></i> Public</span>
                            @endif
                            <span class="endpoint-title">{{ $route['title'] }}</span>
                        </div>
                        <div class="endpoint-body">
                            <p class="endpoint-desc">{{ $route['description'] }}</p>

                            @if(!empty($route['headers']))
                                <div class="section-label">Request Headers</div>
                                <pre>@foreach($route['headers'] as $k => $v){{ $k }}: {{ $v }}
@endforeach</pre>
                            @endif

                            @if(!empty($route['body']))
                                <div class="section-label">Request Payload (JSON)</div>
                                <pre><button class="copy-btn" onclick="copyCode(this)"><i class="fa-regular fa-copy"></i> Copy</button><code>{{ json_encode($route['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                            @endif

                            <div class="section-label">Example Response ({{ $route['response_example']['code'] ?? 200 }} OK)</div>
                            <pre><button class="copy-btn" onclick="copyCode(this)"><i class="fa-regular fa-copy"></i> Copy</button><code style="color: #4ade80;">{{ json_encode($route['response_example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </main>

    <script>
        function filterEndpoints() {
            const query = document.getElementById('apiSearch').value.toLowerCase();
            const cards = document.querySelectorAll('.endpoint-card');
            cards.forEach(card => {
                const searchData = card.getAttribute('data-search');
                card.style.display = searchData.includes(query) ? 'block' : 'none';
            });
        }

        function copyCode(btn) {
            const code = btn.nextElementSibling.innerText;
            navigator.clipboard.writeText(code);
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
            setTimeout(() => { btn.innerHTML = '<i class="fa-regular fa-copy"></i> Copy'; }, 2000);
        }
    </script>
</body>
</html>
