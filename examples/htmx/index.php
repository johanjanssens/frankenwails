<!DOCTYPE html>
<html lang="en">

<head>
    <script>(function(){var t=localStorage.getItem('theme'),d=window.matchMedia('(prefers-color-scheme:dark)').matches;if(t==='dark'||(!t&&d))document.documentElement.classList.add('dark')})()</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTMX Regions - FrankenWails</title>
    <script src="../htmx.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            padding: 40px 20px;
        }
        .container { max-width: 800px; margin: 0 auto; }
        .nav { margin-bottom: 24px; }
        .nav a {
            font-size: 0.85rem;
            color: #666;
            text-decoration: none;
        }
        .nav a:hover { color: #1976d2; }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .card-accent {
            height: 6px;
            background: linear-gradient(90deg, #e74c3c, #f39c12);
        }
        .card-header {
            padding: 24px 32px 0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .card-header h1 {
            font-size: 1.5rem;
            color: #1a1a2e;
            margin-bottom: 4px;
        }
        .card-header .subtitle {
            font-size: 0.9rem;
            color: #888;
        }
        .card-body {
            padding: 24px 32px 32px;
        }
        .region-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 16px;
            height: 400px;
        }
        .region {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 3rem;
            font-weight: bold;
            border-radius: 8px;
            color: #1a1a2e;
        }
        .region-1 { background-color: #bbdefb; }
        .region-2 { background-color: #ef9a9a; }
        .region-3 { background-color: #a5d6a7; }
        .region-4 { background-color: #fff59d; }

        .fade-in { animation: fadeIn 1s ease-in; }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .header-actions { display: flex; gap: 8px; }
        .btn { background: none; border: 1px solid #ddd; border-radius: 6px; padding: 4px 10px; cursor: pointer; font-size: 0.85rem; color: #666; line-height: 1.4; }
        .btn:hover { background: #f0f0f0; }
        .btn-rerun { font-weight: 600; }
        .theme-toggle { font-size: 1.1rem; line-height: 1; }

        html.dark body { background-color: #1a1a2e; color: #e0e0e0; }
        html.dark .card { background: #16213e; box-shadow: 0 2px 12px rgba(0,0,0,0.3); }
        html.dark .card-header h1 { color: #e0e0e0; }
        html.dark .card-header .subtitle { color: #aaa; }
        html.dark .nav a { color: #aaa; }
        html.dark .nav a:hover { color: #64b5f6; }
        html.dark .region { color: #1a1a2e; }
        html.dark .region-1 { background-color: #1565c0; color: white; }
        html.dark .region-2 { background-color: #c62828; color: white; }
        html.dark .region-3 { background-color: #2e7d32; color: white; }
        html.dark .region-4 { background-color: #f9a825; color: #1a1a2e; }
        html.dark .btn { border-color: #444; color: #ccc; }
        html.dark .btn:hover { background: #2a3a5c; }
        footer { text-align: center; color: #999; font-size: 0.85rem; margin-top: 24px; }
        footer a { color: #999; text-decoration: underline; }
        html.dark footer { color: #666; }
        html.dark footer a { color: #666; }
    </style>
    <script>
        document.addEventListener("htmx:beforeSwap", function(event) {
            event.detail.target.classList.add("fade-in");
            setTimeout(() => event.detail.target.classList.remove("fade-in"), 1000);
        });

        function rerun() {
            document.querySelectorAll('.region').forEach(function(el) {
                el.innerHTML = 'Loading...';
                var url = el.getAttribute('hx-get');
                fetch(url).then(function(r) { return r.text(); }).then(function(html) {
                    el.innerHTML = html;
                    el.classList.add('fade-in');
                    setTimeout(function() { el.classList.remove('fade-in'); }, 1000);
                });
            });
        }
    </script>
</head>

<body>
<div class="container">
    <div class="nav">
        <a href="../index.php">&larr; Back to demos</a>
    </div>

    <div class="card">
        <div class="card-accent"></div>
        <div class="card-header">
            <div>
                <h1>HTMX Regions</h1>
                <div class="subtitle">Four regions load concurrently with staggered PHP sleep() delays</div>
            </div>
            <div class="header-actions">
                <button class="btn btn-rerun" onclick="rerun()" aria-label="Re-run">&#x21BB; Re-run</button>
                <button class="btn theme-toggle" onclick="document.documentElement.classList.toggle('dark');localStorage.setItem('theme',document.documentElement.classList.contains('dark')?'dark':'light')" aria-label="Toggle theme">&#x25D1;</button>
            </div>
        </div>
        <div class="card-body">
            <div class="region-grid">
                <div class="region region-1"
                     hx-get="region.php?region=1" hx-trigger="load, rerun" hx-swap="innerHTML">Loading...</div>
                <div class="region region-2"
                     hx-get="region.php?region=2" hx-trigger="load, rerun" hx-swap="innerHTML">Loading...</div>
                <div class="region region-3"
                     hx-get="region.php?region=3" hx-trigger="load, rerun" hx-swap="innerHTML">Loading...</div>
                <div class="region region-4"
                     hx-get="region.php?region=4" hx-trigger="load, rerun" hx-swap="innerHTML">Loading...</div>
            </div>
        </div>
    </div>
    <footer>hack'd by <a href="https://bsky.app/profile/johanjanssens.bsky.social" target="_blank">Johan Janssens</a></footer>
</div>
</body>
</html>
