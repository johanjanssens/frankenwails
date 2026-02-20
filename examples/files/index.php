<?php
$baseDir = dirname(dirname(__DIR__)); // frankenwails root
$requestedPath = $_GET['path'] ?? '';

// Resolve and sanitize path
$currentPath = realpath($baseDir . '/' . $requestedPath);
if (!$currentPath || strpos($currentPath, $baseDir) !== 0) {
    $currentPath = $baseDir;
    $requestedPath = '';
}

$isDir = is_dir($currentPath);

// Build breadcrumbs
$relativePath = $requestedPath ? ltrim(str_replace($baseDir, '', $currentPath), '/') : '';
$breadcrumbs = [];
if ($relativePath) {
    $parts = explode('/', $relativePath);
    $accumulated = '';
    foreach ($parts as $part) {
        $accumulated .= ($accumulated ? '/' : '') . $part;
        $breadcrumbs[] = ['name' => $part, 'path' => $accumulated];
    }
}

// Read directory
$entries = [];
if ($isDir) {
    $items = @scandir($currentPath);
    if ($items) {
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $fullPath = $currentPath . '/' . $item;
            $entries[] = [
                'name' => $item,
                'is_dir' => is_dir($fullPath),
                'size' => is_file($fullPath) ? filesize($fullPath) : null,
                'modified' => filemtime($fullPath),
                'path' => ($requestedPath ? $requestedPath . '/' : '') . $item,
            ];
        }
    }
    // Sort: directories first, then by name
    usort($entries, function($a, $b) {
        if ($a['name'] === '..') return -1;
        if ($b['name'] === '..') return 1;
        if ($a['is_dir'] !== $b['is_dir']) return $b['is_dir'] - $a['is_dir'];
        return strcasecmp($a['name'], $b['name']);
    });
}

// Read file preview
$fileContent = null;
$fileSize = null;
if (!$isDir) {
    $fileSize = filesize($currentPath);
    $ext = strtolower(pathinfo($currentPath, PATHINFO_EXTENSION));
    $textExts = ['php','go','mod','sum','txt','md','html','css','js','json','yaml','yml','toml','ini','sh','sql','xml','env','makefile','gitignore','lock'];
    $basename = strtolower(basename($currentPath));
    if (in_array($ext, $textExts) || in_array($basename, ['makefile', '.gitignore', '.env'])) {
        if ($fileSize < 64 * 1024) { // 64KB limit
            $fileContent = file_get_contents($currentPath);
        } else {
            $fileContent = "File too large to preview (" . formatSize($fileSize) . ")";
        }
    }
}

function formatSize($bytes) {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 0) . ' KB';
    return $bytes . ' B';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>(function(){var t=localStorage.getItem('theme'),d=window.matchMedia('(prefers-color-scheme:dark)').matches;if(t==='dark'||(!t&&d))document.documentElement.classList.add('dark')})()</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Files - FrankenWails</title>
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
        .nav a { font-size: 0.85rem; color: #666; text-decoration: none; }
        .nav a:hover { color: #1976d2; }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .card-accent {
            height: 6px;
            background: linear-gradient(90deg, #e67e22, #27ae60);
        }
        .card-header {
            padding: 24px 32px 0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .card-header h1 { font-size: 1.5rem; color: #1a1a2e; margin-bottom: 4px; }
        .card-header .subtitle { font-size: 0.9rem; color: #888; }
        .card-body { padding: 24px 32px 32px; }
        .breadcrumbs {
            font-size: 0.85rem;
            margin-bottom: 16px;
            padding: 10px 14px;
            background: #f8f9fa;
            border-radius: 8px;
            font-family: 'SF Mono', 'Fira Code', monospace;
        }
        .breadcrumbs a { color: #1976d2; text-decoration: none; }
        .breadcrumbs a:hover { text-decoration: underline; }
        .breadcrumbs .sep { color: #ccc; margin: 0 4px; }
        .file-list { list-style: none; }
        .file-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .file-item:last-child { border-bottom: none; }
        .file-icon { font-size: 1.1rem; width: 24px; text-align: center; flex-shrink: 0; }
        .file-name {
            flex: 1;
            text-decoration: none;
            color: #333;
            font-size: 0.9rem;
        }
        .file-name:hover { color: #1976d2; }
        .file-name.dir { font-weight: 600; }
        .file-meta {
            font-size: 0.75rem;
            color: #aaa;
            white-space: nowrap;
        }
        .file-preview {
            background: #1a1a2e;
            color: #e0e0e0;
            padding: 20px;
            border-radius: 8px;
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 0.8rem;
            line-height: 1.5;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-word;
            max-height: 500px;
            overflow-y: auto;
        }
        .file-info {
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 12px;
        }
        .theme-toggle { background: none; border: 1px solid #ddd; border-radius: 6px; padding: 4px 10px; cursor: pointer; font-size: 1.1rem; color: #666; line-height: 1; }
        .theme-toggle:hover { background: #f0f0f0; }

        html.dark body { background-color: #1a1a2e; color: #e0e0e0; }
        html.dark .card { background: #16213e; box-shadow: 0 2px 12px rgba(0,0,0,0.3); }
        html.dark .card-header h1 { color: #e0e0e0; }
        html.dark .card-header .subtitle { color: #aaa; }
        html.dark .breadcrumbs { background: #1a1a2e; }
        html.dark .breadcrumbs a { color: #64b5f6; }
        html.dark .breadcrumbs .sep { color: #555; }
        html.dark .file-item { border-color: #2a3a5c; }
        html.dark .file-name { color: #e0e0e0; }
        html.dark .file-name:hover { color: #64b5f6; }
        html.dark .file-meta { color: #666; }
        html.dark .file-preview { background: #0d1117; }
        html.dark .file-info { color: #666; }
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
        <div class="card-header">
            <div>
                <h1>File Browser</h1>
                <div class="subtitle">Browsing the local filesystem with PHP</div>
            </div>
            <button class="theme-toggle" onclick="document.documentElement.classList.toggle('dark');localStorage.setItem('theme',document.documentElement.classList.contains('dark')?'dark':'light')" aria-label="Toggle theme">&#x25D1;</button>
        </div>
        <div class="card-body">
            <div class="breadcrumbs">
                <a href="index.php">frankenwails</a>
                <?php foreach ($breadcrumbs as $crumb): ?>
                    <span class="sep">/</span>
                    <a href="index.php?path=<?= urlencode($crumb['path']) ?>"><?= htmlspecialchars($crumb['name']) ?></a>
                <?php endforeach; ?>
            </div>

            <?php if ($isDir): ?>
                <ul class="file-list">
                    <?php foreach ($entries as $entry): ?>
                        <li class="file-item">
                            <span class="file-icon"><?= $entry['is_dir'] ? '&#x1F4C1;' : '&#x1F4C4;' ?></span>
                            <a class="file-name <?= $entry['is_dir'] ? 'dir' : '' ?>"
                               href="index.php?path=<?= urlencode($entry['path']) ?>"><?= htmlspecialchars($entry['name']) ?></a>
                            <span class="file-meta">
                                <?= $entry['size'] !== null ? formatSize($entry['size']) : '' ?>
                            </span>
                            <span class="file-meta"><?= date('M j, H:i', $entry['modified']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="file-info">
                    <?= htmlspecialchars(basename($currentPath)) ?> &mdash; <?= formatSize($fileSize) ?>
                    &mdash; Modified <?= date('M j Y, H:i', filemtime($currentPath)) ?>
                </div>
                <?php if ($fileContent !== null): ?>
                    <div class="file-preview"><?= htmlspecialchars($fileContent) ?></div>
                <?php else: ?>
                    <div class="file-preview">Binary file — preview not available</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <footer>hack'd by <a href="https://bsky.app/profile/johanjanssens.bsky.social" target="_blank">Johan Janssens</a></footer>
</div>
</body>
</html>
