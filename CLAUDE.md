# FrankenWails

PHP as a native desktop app using FrankenPHP + Wails. No HTTP server.

## Architecture

- `main.go` — Wails app entry point. Initializes FrankenPHP, creates an `http.Handler` that serves PHP, and passes it to Wails' `AssetServer`. The WebView routes all requests through this handler in-process (no TCP).
- `examples/` — PHP demo pages served as the document root.
  - `calculator/` — HTMX form posting to PHP (addition)
  - `htmx/` — Four regions loading concurrently with staggered delays
  - `todo/` — SQLite-backed todo list (CRUD)
  - `files/` — Local filesystem browser with file preview

## Build

```bash
make php       # Build PHP static library (first time only)
make build     # Build the Go binary (dist/frankenwails)
make run       # Build + launch the desktop app
make env       # Auto-generate env.yaml for GoLand
```

Build tags: `nowatcher desktop dev` (required for Wails + FrankenPHP).

- `nowatcher` — disables FrankenPHP's file watcher (not needed for desktop app, avoids building the watcher C++ dependency)
- `desktop` — required by Wails at runtime
- `dev` — required by Wails for development mode

The Go binary requires CGO with PHP headers. CGO flags are resolved from `build/php`. If `env.yaml` exists, the build skips flag resolution and assumes the environment is already configured (e.g., pointing to an external PHP build).

For GoLand, run `make env` to generate `env.yaml`, then configure GoLand to load it.

## Key Patterns

### Wails AssetServer (no HTTP server)

Wails intercepts all WebView navigation and routes it through a Go `http.Handler`. This is the core differentiator — FrankenPHP executes PHP without any network layer:

```go
wails.Run(&options.App{
    AssetServer: &assetserver.Options{
        Handler: handler,        // FrankenPHP handler
        Middleware: func(next http.Handler) http.Handler {
            return handler       // Override Wails' default static handler
        },
    },
})
```

### Request Flow

```
WebView URL request
    → Wails AssetServer intercept (in-process, no TCP)
    → http.Handler
    → Static file? → http.ServeFile()
    → .php file?   → frankenphp.NewRequestWithContext() → frankenphp.ServeHTTP()
```

### Wails Constraints

- **No external URLs**: The WebView is sandboxed. All assets must be served through the handler.
- **Inspector**: Set `Debug: options.Debug{OpenInspectorOnStartup: true}` during development to get WebKit/Chromium DevTools.

## Adding a New Demo

1. Create a directory under `examples/` (e.g., `examples/mydemo/`)
2. Add `index.php` with the standard page layout (card design, dark mode, nav back link)
3. Reference `../htmx.js` if HTMX is needed (shared copy in `examples/`)
4. Add a card to `examples/index.php`

## Conventions

- Demo pages go in `examples/`
- All pages use the same CSS patterns: light/dark theme toggle, card with accent gradient, back navigation
- Keep `main.go` minimal — this is a demo, not a framework
- Static files (CSS, JS, images) are served directly via `http.ServeFile`, only `.php` goes through FrankenPHP
