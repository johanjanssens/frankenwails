package main

import (
	"log/slog"
	"net/http"
	"os"
	"path/filepath"
	"strings"
	"time"

	"github.com/dunglas/frankenphp"
	"github.com/joho/godotenv"
	"github.com/lmittmann/tint"
	"github.com/wailsapp/wails/v2"
	"github.com/wailsapp/wails/v2/pkg/options"
	"github.com/wailsapp/wails/v2/pkg/options/assetserver"
)

func main() {
	// Load .env if present
	_ = godotenv.Load()

	// Set up logger
	logger := slog.New(tint.NewHandler(os.Stdout, &tint.Options{
		Level:      slog.LevelDebug,
		TimeFormat: time.Kitchen,
	}))
	slog.SetDefault(logger)

	// Resolve document root
	docRoot, err := filepath.Abs("examples")
	if err != nil {
		logger.Error("Failed to resolve document root", "error", err)
		os.Exit(1)
	}

	// Init FrankenPHP
	if err := frankenphp.Init(
		frankenphp.WithNumThreads(4),
		frankenphp.WithLogger(logger),
		frankenphp.WithPhpIni(map[string]string{
			"include_path": docRoot,
		}),
	); err != nil {
		logger.Error("Failed to initialize FrankenPHP", "error", err)
		os.Exit(1)
	}
	defer frankenphp.Shutdown()

	// Create the handler that serves PHP via FrankenPHP
	handler := http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		// Rewrite directory requests to index.php
		if r.URL.Path == "/" || strings.HasSuffix(r.URL.Path, "/") {
			r.URL.Path = r.URL.Path + "index.php"
		}

		// Serve static files directly (CSS, JS, images, etc.)
		ext := filepath.Ext(r.URL.Path)
		if ext != "" && ext != ".php" {
			http.ServeFile(w, r, filepath.Join(docRoot, r.URL.Path))
			return
		}

		// Create FrankenPHP request
		req, err := frankenphp.NewRequestWithContext(r,
			frankenphp.WithRequestResolvedDocumentRoot(docRoot),
			frankenphp.WithRequestLogger(logger),
		)
		if err != nil {
			logger.Error("Failed to create FrankenPHP request", "error", err)
			http.Error(w, "Internal server error", http.StatusInternalServerError)
			return
		}

		if err := frankenphp.ServeHTTP(w, req); err != nil {
			logger.Error("Failed to serve PHP", "error", err)
		}
	})

	logger.Info("Starting FrankenWails", "docroot", docRoot)

	// Run Wails — no HTTP server, the WebView routes requests through the handler
	err = wails.Run(&options.App{
		Title:  "FrankenWails",
		Width:  1024,
		Height: 768,
		AssetServer: &assetserver.Options{
			Middleware: func(next http.Handler) http.Handler {
				return handler
			},
		},
		Debug: options.Debug{
			OpenInspectorOnStartup: true,
		},
	})

	if err != nil {
		logger.Error("Wails error", "error", err)
		os.Exit(1)
	}
}
