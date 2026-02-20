<?php
$phpVersion = phpversion();
$os = php_uname('s') . ' ' . php_uname('m');
$pid = getmypid();
$memory = round(memory_get_usage(true) / 1024 / 1024, 1);
$extensions = get_loaded_extensions();
sort($extensions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>(function(){var t=localStorage.getItem('theme'),d=window.matchMedia('(prefers-color-scheme:dark)').matches;if(t==='dark'||(!t&&d))document.documentElement.classList.add('dark')})()</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FrankenWails</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            padding: 40px 20px;
        }
        .header {
            max-width: 1200px;
            margin: 0 auto 40px;
            text-align: center;
            position: relative;
        }
        .header h1 {
            font-size: 2.5rem;
            color: #1a1a2e;
            margin-bottom: 8px;
        }
        .header p {
            font-size: 1.1rem;
            color: #666;
        }
        .runtime-bar {
            max-width: 1200px;
            margin: 0 auto 30px;
            background: white;
            border-radius: 8px;
            padding: 16px 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .runtime-bar .label {
            font-weight: 600;
            color: #444;
            font-size: 0.9rem;
        }
        .runtime-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #e8f4fd;
            color: #1976d2;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 24px;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        .card-accent {
            height: 6px;
        }
        .card-body {
            padding: 24px;
        }
        .card-body h2 {
            font-size: 1.3rem;
            margin-bottom: 8px;
            color: #1a1a2e;
        }
        .card-body p {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 16px;
        }
        .card-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .tag {
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .tag-htmx { background: #e0f2f1; color: #00695c; }
        .tag-php { background: #ede7f6; color: #4527a0; }
        .tag-wails { background: #fff8e1; color: #f57f17; }
        .tag-sqlite { background: #e8f5e9; color: #2e7d32; }
        .tag-fs { background: #f3e5f5; color: #7b1fa2; }
        @media (max-width: 768px) {
            .grid { grid-template-columns: 1fr; }
            .header h1 { font-size: 2rem; }
        }
        .theme-toggle { background: none; border: 1px solid #ddd; border-radius: 6px; padding: 4px 10px; cursor: pointer; font-size: 1.1rem; color: #666; line-height: 1; position: absolute; top: 0; right: 0; }
        .theme-toggle:hover { background: #f0f0f0; }
        html.dark body { background-color: #1a1a2e; color: #e0e0e0; }
        html.dark .header h1 { color: #e0e0e0; }
        html.dark .header p { color: #aaa; }
        html.dark .runtime-bar { background: #16213e; box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
        html.dark .runtime-bar .label { color: #ccc; }
        html.dark .runtime-badge { background: #1e2a45; color: #64b5f6; }
        html.dark .card { background: #16213e; box-shadow: 0 2px 12px rgba(0,0,0,0.3); }
        html.dark .card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.4); }
        html.dark .card-body h2 { color: #e0e0e0; }
        html.dark .card-body p { color: #aaa; }
        html.dark .tag-htmx { background: #1a332e; color: #4db6ac; }
        html.dark .tag-php { background: #1f1a33; color: #b39ddb; }
        html.dark .tag-wails { background: #33301a; color: #ffd54f; }
        html.dark .tag-sqlite { background: #1a332e; color: #81c784; }
        html.dark .tag-fs { background: #2a1a33; color: #ce93d8; }
        html.dark .theme-toggle { border-color: #444; color: #ccc; }
        html.dark .theme-toggle:hover { background: #2a3a5c; }
        footer { max-width: 1200px; margin: 40px auto 20px; text-align: center; color: #999; font-size: 0.85rem; }
        footer a { color: #999; text-decoration: underline; }
        html.dark footer { color: #666; }
        html.dark footer a { color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FrankenWails</h1>
        <p>PHP as a native desktop app &mdash; powered by FrankenPHP + Wails</p>
        <button class="theme-toggle" onclick="document.documentElement.classList.toggle('dark');localStorage.setItem('theme',document.documentElement.classList.contains('dark')?'dark':'light')" aria-label="Toggle theme">&#x25D1;</button>
    </div>

    <div class="runtime-bar">
        <span class="label">Runtime:</span>
        <span class="runtime-badge">PHP <?= $phpVersion ?></span>
        <span class="runtime-badge"><?= php_sapi_name() ?></span>
        <span class="runtime-badge"><?= $os ?></span>
        <span class="runtime-badge">PID <?= $pid ?></span>
        <span class="runtime-badge"><?= $memory ?> MB</span>
    </div>

    <div class="grid">
        <a href="calculator/index.php" class="card">
            <div class="card-accent" style="background: linear-gradient(90deg, #3498db, #2ecc71)"></div>
            <div class="card-body">
                <h2>Calculator</h2>
                <p>HTMX form that POSTs two numbers to PHP and displays the sum inline. No page reload, no server — just in-process PHP.</p>
                <div class="card-tags">
                    <span class="tag tag-htmx">HTMX</span>
                    <span class="tag tag-php">PHP</span>
                </div>
            </div>
        </a>

        <a href="htmx/index.php" class="card">
            <div class="card-accent" style="background: linear-gradient(90deg, #e74c3c, #f39c12)"></div>
            <div class="card-body">
                <h2>HTMX Regions</h2>
                <p>Four colored quadrants load independently via HTMX with staggered delays, demonstrating concurrent PHP execution.</p>
                <div class="card-tags">
                    <span class="tag tag-htmx">HTMX</span>
                    <span class="tag tag-php">PHP</span>
                    <span class="tag tag-wails">Async</span>
                </div>
            </div>
        </a>

        <a href="todo/index.php" class="card">
            <div class="card-accent" style="background: linear-gradient(90deg, #9b59b6, #3498db)"></div>
            <div class="card-body">
                <h2>Todo</h2>
                <p>Local task list backed by SQLite. Add, complete, and delete todos — data persists across app restarts.</p>
                <div class="card-tags">
                    <span class="tag tag-php">PHP</span>
                    <span class="tag tag-sqlite">SQLite</span>
                </div>
            </div>
        </a>

        <a href="files/index.php" class="card">
            <div class="card-accent" style="background: linear-gradient(90deg, #e67e22, #27ae60)"></div>
            <div class="card-body">
                <h2>File Browser</h2>
                <p>Browse the local filesystem where the app was launched. Navigate directories and preview text files.</p>
                <div class="card-tags">
                    <span class="tag tag-php">PHP</span>
                    <span class="tag tag-fs">Filesystem</span>
                </div>
            </div>
        </a>
    </div>

    <footer>hack'd by <a href="https://bsky.app/profile/johanjanssens.bsky.social" target="_blank">Johan Janssens</a></footer>
</body>
</html>
