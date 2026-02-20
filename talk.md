# Building Native Desktop Apps with PHP

> Base narrative for FrankenPHP conference talks

### History

- [Building Desktop Apps with PHP](https://confoo.ca/en/2025/session/building-desktop-apps-with-php) ([slides](https://gamma.app/docs/Building-Desktop-Apps-with-PHP-Confoo-2025-zeyd7c4qykll78b)) — ConFoo 2025 (initial exploration of the concept)
- [Building Native Desktop Apps with PHP](https://phptek.io/) — php[tek] 2026 (deep dive with FrankenPHP + Wails)

## Abstract

PHP is traditionally a server-side language — but what if you could use it to build desktop
applications for Windows, Linux, and macOS?

In this talk, we'll explore how FrankenPHP, Go, and Wails can bring PHP into the world of native
applications. You'll see how PHP can run outside the web server while integrating with lightweight,
cross-platform UI frameworks.

We'll start with FrankenPHP, the modern PHP runtime built on Go, showing how it allows PHP to run
efficiently on the desktop. Then, we'll dive into Wails, a Go-based framework for building desktop
apps with HTML, CSS, and JavaScript.

You'll learn how FrankenPHP and Wails can be combined to create fast, self-contained desktop
applications with PHP powering the backend — no Node.js, Electron, or traditional server setup
required.

Whether you're a seasoned PHP developer or just curious, this talk will inspire you to take PHP
beyond the web.

## The Problem

PHP lives on the server. It powers the web, but the moment you need a desktop application you're
expected to reach for Electron, .NET, Java, or Swift. These are fine tools, but they mean leaving
PHP behind — along with all the knowledge, libraries, and muscle memory you've built over the years.

What if you didn't have to?

## The Key Insight

FrankenPHP embeds PHP as a library inside a Go process. Normally that Go process runs an HTTP
server. But there's nothing stopping you from routing those PHP requests somewhere else — like
a native desktop window.

That's exactly what Wails does. Wails creates a native WebView (WebKit on macOS, WebView2 on
Windows, WebKitGTK on Linux) and intercepts every URL request the WebView makes. Instead of
hitting a network socket, those requests are routed directly to a Go `http.Handler` — in-process,
no TCP, no port, no server.

```
WebView navigates to /index.php
    -> Wails AssetServer intercepts (no TCP, no network)
    -> Go http.Handler routes to FrankenPHP
    -> PHP executes the script
    -> HTML response rendered in WebView
```

The WebView thinks it's making HTTP requests, but they're just function calls. The entire
application is a single binary.

## What This Means

- **No HTTP server** — there is literally no server running. No port, no socket, no network stack.
- **No Electron** — no bundled Chromium, no Node.js runtime. The binary is small and starts fast.
- **No new language** — your UI is PHP generating HTML. Your interactivity is HTMX or vanilla JS.
  The same skills you use every day.
- **Single binary** — PHP runtime, Go runtime, WebView, and your application code all compile into
  one executable. Distribute it like any native app.
- **Cross-platform** — Wails supports macOS, Windows, and Linux from the same codebase.

## The Architecture

The entire application is ~100 lines of Go:

1. **Initialize FrankenPHP** — set up the PHP runtime with threads and configuration
2. **Create an `http.Handler`** — route `.php` files to FrankenPHP, static files to `http.ServeFile`
3. **Pass the handler to Wails** — Wails' `AssetServer` intercepts WebView requests and routes
   them through the handler
4. **Done** — `wails.Run()` opens the native window

```go
handler := http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
    ext := filepath.Ext(r.URL.Path)
    if ext != "" && ext != ".php" {
        http.ServeFile(w, r, filepath.Join(docRoot, r.URL.Path))
        return
    }
    req, _ := frankenphp.NewRequestWithContext(r,
        frankenphp.WithRequestResolvedDocumentRoot(docRoot),
    )
    frankenphp.ServeHTTP(w, req)
})

wails.Run(&options.App{
    Title: "FrankenWails",
    AssetServer: &assetserver.Options{
        Middleware: func(next http.Handler) http.Handler {
            return handler
        },
    },
})
```

That's it. PHP is now a desktop runtime.

## What PHP Can Do on the Desktop

Everything it can do on the server — and more, because it has direct access to the local machine:

- **SQLite** — local database, no server needed. A todo app with persistent storage in 50 lines.
- **Filesystem access** — browse directories, read files, display previews. PHP's `scandir()` and
  `file_get_contents()` work on the user's actual filesystem.
- **HTMX** — interactive UIs without page reloads. Forms, live updates, concurrent loading regions.
  All the patterns you know from server-side HTMX work identically.
- **Sessions, cookies, headers** — the full HTTP abstraction is preserved. PHP doesn't know it's
  not running behind Apache.

## Demo Walkthrough

### Calculator — HTMX form posting

An HTMX form that POSTs two numbers to a PHP script and displays the sum inline. No page reload,
no JavaScript framework. The PHP script is `<?= $_POST["num1"] + $_POST["num2"] ?>` — one line.

This demonstrates that the full HTTP request/response cycle works: POST data, content types,
response bodies. PHP processes the form exactly like it would on a web server.

### HTMX Regions — concurrent PHP execution

Four colored regions load independently via HTMX, each calling a PHP script with a different
`sleep()` delay. They load concurrently because FrankenPHP runs multiple threads.

This proves that the desktop app isn't single-threaded — multiple PHP scripts execute in parallel,
just like they would on a real server handling concurrent requests.

### Todo — SQLite persistence

A full CRUD todo list backed by SQLite. Add, complete, delete tasks — data persists across app
restarts because it's stored in a local database file.

This shows PHP's database extensions work. `new SQLite3()`, prepared statements, all of it. The
data lives on the user's machine, not a remote server.

### File Browser — local filesystem

Browse the directory where the app was launched. Navigate folders, view file metadata, preview
text files. PHP's `scandir()`, `file_get_contents()`, `realpath()` all work against the real
filesystem.

This is something a web app typically can't do — direct local filesystem access without upload
dialogs or browser sandboxing. The desktop context makes it natural.

## Why This Matters

PHP has 30 years of ecosystem. Millions of developers. Libraries for everything. The only thing
it couldn't do was run on the desktop.

Now it can. Not through a hack or a shim — through a clean architectural integration where the
PHP runtime sits inside a native application, processing requests the same way it always has.
The WebView is the browser. FrankenPHP is the server. Except there is no server.

This opens up a new category of applications:

- **Internal tools** — admin panels, data browsers, log viewers that run locally
- **Developer tools** — database managers, API testers, config editors built with PHP
- **Offline-first apps** — applications that work without internet, storing data in SQLite
- **Kiosk/POS systems** — single-purpose desktop apps that teams can build with existing PHP skills

## Key Takeaway

PHP doesn't have to stay on the server. FrankenPHP gives it a Go runtime, Wails gives it a native
window, and together they create a desktop application framework where PHP developers feel right
at home. Same language, same patterns, same ecosystem — new platform.

---

This work is licensed under [CC BY 4.0](https://creativecommons.org/licenses/by/4.0/).
You are free to share and adapt this material with appropriate attribution.
