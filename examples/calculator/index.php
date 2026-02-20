<!DOCTYPE html>
<html lang="en">

<head>
    <script>(function(){var t=localStorage.getItem('theme'),d=window.matchMedia('(prefers-color-scheme:dark)').matches;if(t==='dark'||(!t&&d))document.documentElement.classList.add('dark')})()</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculator - FrankenWails</title>
    <script src="../htmx.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .container { max-width: 480px; width: 100%; }
        .nav {
            margin-bottom: 24px;
        }
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
            background: linear-gradient(90deg, #3498db, #2ecc71);
        }
        .card-body {
            padding: 32px;
        }
        .card-body h1 {
            font-size: 1.5rem;
            color: #1a1a2e;
            margin-bottom: 4px;
        }
        .card-body .subtitle {
            font-size: 0.9rem;
            color: #888;
            margin-bottom: 24px;
        }
        label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #888;
            margin-bottom: 6px;
        }
        input[type="number"] {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            margin-bottom: 16px;
            outline: none;
            transition: border-color 0.2s;
        }
        input[type="number"]:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        button {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover { background: #2980b9; }
        .result {
            margin-top: 20px;
            padding: 16px;
            background: #e8f4fd;
            border-radius: 8px;
            text-align: center;
            font-size: 1.1rem;
            color: #1976d2;
        }
        .result .label { font-size: 0.75rem; color: #888; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
        .result .value { font-size: 1.5rem; font-weight: 700; color: #1976d2; }
        .header-row { display: flex; justify-content: space-between; align-items: flex-start; }
        .theme-toggle { background: none; border: 1px solid #ddd; border-radius: 6px; padding: 4px 10px; cursor: pointer; font-size: 1.1rem; color: #666; line-height: 1; width: auto; }
        .theme-toggle:hover { background: #f0f0f0; }

        html.dark body { background-color: #1a1a2e; color: #e0e0e0; }
        html.dark .card { background: #16213e; box-shadow: 0 2px 12px rgba(0,0,0,0.3); }
        html.dark .card-body h1 { color: #e0e0e0; }
        html.dark .card-body .subtitle { color: #aaa; }
        html.dark label { color: #aaa; }
        html.dark input[type="number"] { background: #1a1a2e; border-color: #2a3a5c; color: #e0e0e0; }
        html.dark input[type="number"]:focus { border-color: #3498db; box-shadow: 0 0 0 3px rgba(52,152,219,0.2); }
        html.dark .result { background: #1e2a45; }
        html.dark .result .value { color: #64b5f6; }
        html.dark .nav a { color: #aaa; }
        html.dark .nav a:hover { color: #64b5f6; }
        html.dark .theme-toggle { border-color: #444; color: #ccc; }
        html.dark .theme-toggle:hover { background: #2a3a5c; }
        footer { text-align: center; color: #999; font-size: 0.85rem; margin-top: 24px; }
        footer a { color: #999; text-decoration: underline; }
        html.dark footer { color: #666; }
        html.dark footer a { color: #666; }
    </style>
</head>

<body>
<div class="container">
    <div class="nav">
        <a href="../index.php">&larr; Back to demos</a>
    </div>

    <div class="card">
        <div class="card-accent"></div>
        <div class="card-body">
            <div class="header-row">
                <div>
                    <h1>Calculator</h1>
                    <div class="subtitle">HTMX form posting to PHP — no page reload</div>
                </div>
                <button class="theme-toggle" onclick="document.documentElement.classList.toggle('dark');localStorage.setItem('theme',document.documentElement.classList.contains('dark')?'dark':'light')" aria-label="Toggle theme">&#x25D1;</button>
            </div>

            <form hx-post="calculator.php" hx-trigger="submit" hx-target="#result-value" hx-swap="innerHTML">
                <label for="num1">Number 1</label>
                <input type="number" id="num1" name="num1" value="" required>

                <label for="num2">Number 2</label>
                <input type="number" id="num2" name="num2" value="" required>

                <button type="submit">Add</button>
            </form>

            <div class="result">
                <div class="label">Result</div>
                <div class="value" id="result-value">&mdash;</div>
            </div>
        </div>
    </div>
    <footer>hack'd by <a href="https://bsky.app/profile/johanjanssens.bsky.social" target="_blank">Johan Janssens</a></footer>
</div>
</body>
</html>
