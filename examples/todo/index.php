<?php
$dbFile = __DIR__ . '/todo.db';
$db = new SQLite3($dbFile);
$db->exec('CREATE TABLE IF NOT EXISTS todos (id INTEGER PRIMARY KEY AUTOINCREMENT, text TEXT NOT NULL, done INTEGER DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)');

// Handle actions
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($action === 'add' && !empty($_POST['text'])) {
    $stmt = $db->prepare('INSERT INTO todos (text) VALUES (:text)');
    $stmt->bindValue(':text', $_POST['text'], SQLITE3_TEXT);
    $stmt->execute();
}

if ($action === 'toggle' && isset($_GET['id'])) {
    $db->exec('UPDATE todos SET done = NOT done WHERE id = ' . (int)$_GET['id']);
}

if ($action === 'delete' && isset($_GET['id'])) {
    $db->exec('DELETE FROM todos WHERE id = ' . (int)$_GET['id']);
}

// Fetch todos
$results = $db->query('SELECT * FROM todos ORDER BY done ASC, created_at DESC');
$todos = [];
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $todos[] = $row;
}

$total = count($todos);
$done = count(array_filter($todos, fn($t) => $t['done']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>(function(){var t=localStorage.getItem('theme'),d=window.matchMedia('(prefers-color-scheme:dark)').matches;if(t==='dark'||(!t&&d))document.documentElement.classList.add('dark')})()</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo - FrankenWails</title>
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
        .container { max-width: 520px; width: 100%; }
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
            background: linear-gradient(90deg, #9b59b6, #3498db);
        }
        .card-body { padding: 32px; }
        .header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px; }
        .card-body h1 { font-size: 1.5rem; color: #1a1a2e; }
        .subtitle { font-size: 0.9rem; color: #888; margin-bottom: 20px; }
        .add-form {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }
        .add-form input[type="text"] {
            flex: 1;
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
        }
        .add-form input[type="text"]:focus {
            border-color: #9b59b6;
            box-shadow: 0 0 0 3px rgba(155,89,182,0.1);
        }
        .add-form button {
            padding: 10px 18px;
            background: #9b59b6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
        }
        .add-form button:hover { background: #8e44ad; }
        .stats {
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eee;
        }
        .todo-list { list-style: none; }
        .todo-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .todo-item:last-child { border-bottom: none; }
        .todo-item a { text-decoration: none; }
        .todo-check {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            font-size: 0.7rem;
            color: transparent;
        }
        .todo-check:hover { border-color: #9b59b6; }
        .todo-check.done {
            background: #9b59b6;
            border-color: #9b59b6;
            color: white;
        }
        .todo-text { flex: 1; font-size: 0.95rem; }
        .todo-text.done { text-decoration: line-through; color: #aaa; }
        .todo-delete {
            color: #ccc;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .todo-delete:hover { color: #e74c3c; background: #fce4ec; }
        .empty { text-align: center; color: #aaa; padding: 32px 0; font-size: 0.95rem; }
        .db-path { font-size: 0.7rem; color: #bbb; margin-top: 16px; word-break: break-all; font-family: monospace; }
        .theme-toggle { background: none; border: 1px solid #ddd; border-radius: 6px; padding: 4px 10px; cursor: pointer; font-size: 1.1rem; color: #666; line-height: 1; }
        .theme-toggle:hover { background: #f0f0f0; }

        html.dark body { background-color: #1a1a2e; color: #e0e0e0; }
        html.dark .card { background: #16213e; box-shadow: 0 2px 12px rgba(0,0,0,0.3); }
        html.dark .card-body h1 { color: #e0e0e0; }
        html.dark .subtitle { color: #aaa; }
        html.dark .stats { color: #aaa; border-color: #2a3a5c; }
        html.dark .add-form input[type="text"] { background: #1a1a2e; border-color: #2a3a5c; color: #e0e0e0; }
        html.dark .add-form input[type="text"]:focus { border-color: #9b59b6; }
        html.dark .todo-item { border-color: #2a3a5c; }
        html.dark .todo-text { color: #e0e0e0; }
        html.dark .todo-text.done { color: #666; }
        html.dark .todo-check { border-color: #444; }
        html.dark .todo-delete { color: #555; }
        html.dark .todo-delete:hover { color: #ef9a9a; background: #3e1a1a; }
        html.dark .empty { color: #666; }
        html.dark .db-path { color: #555; }
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
                <h1>Todo</h1>
                <button class="theme-toggle" onclick="document.documentElement.classList.toggle('dark');localStorage.setItem('theme',document.documentElement.classList.contains('dark')?'dark':'light')" aria-label="Toggle theme">&#x25D1;</button>
            </div>
            <div class="subtitle">Local SQLite-backed task list</div>

            <form class="add-form" method="post" action="index.php">
                <input type="hidden" name="action" value="add">
                <input type="text" name="text" placeholder="What needs to be done?" autocomplete="off" autofocus>
                <button type="submit">Add</button>
            </form>

            <?php if ($total > 0): ?>
                <div class="stats"><?= $done ?> of <?= $total ?> completed</div>
                <ul class="todo-list">
                    <?php foreach ($todos as $todo): ?>
                        <li class="todo-item">
                            <a href="index.php?action=toggle&id=<?= $todo['id'] ?>">
                                <div class="todo-check <?= $todo['done'] ? 'done' : '' ?>"><?= $todo['done'] ? '&#x2713;' : '' ?></div>
                            </a>
                            <span class="todo-text <?= $todo['done'] ? 'done' : '' ?>"><?= htmlspecialchars($todo['text']) ?></span>
                            <a href="index.php?action=delete&id=<?= $todo['id'] ?>" class="todo-delete">&times;</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty">No todos yet. Add one above!</div>
            <?php endif; ?>

            <div class="db-path">db: <?= $dbFile ?></div>
        </div>
    </div>
    <footer>hack'd by <a href="https://bsky.app/profile/johanjanssens.bsky.social" target="_blank">Johan Janssens</a></footer>
</div>
</body>
</html>
