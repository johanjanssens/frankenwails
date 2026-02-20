export MAKEFLAGS='--silent --environment-override'

ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST))))
PHP  := $(ROOT)/build/php

.ONESHELL:

.PHONY: php
php:
	make -C $(PHP) download
	make -C $(PHP) build

.PHONY: build
build:
	if [ ! -f $(ROOT)/env.yaml ]; then
		if [ "$$(uname -s)" = "Darwin" ]; then
			export MACOSX_DEPLOYMENT_TARGET="15.0"
		fi
		export CGO_CFLAGS="$$(make -C $(PHP) cflags)"
		export CGO_CPPFLAGS="$$CGO_CFLAGS"
		export CGO_LDFLAGS="$$(make -C $(PHP) ldflags)"
	fi

	cd $(ROOT)
	CGO_ENABLED=1 go build -tags "nowatcher desktop dev" -o dist/frankenwails .
	echo "Built dist/frankenwails"

.PHONY: run
run: build
	cd $(ROOT) && ./dist/frankenwails

.PHONY: env
env:
	if [ "$$(uname -s)" = "Darwin" ]; then \
		deployment_target='MACOSX_DEPLOYMENT_TARGET: "15.0"'; \
	else \
		deployment_target=""; \
	fi; \
	cflags=$$(make -C $(PHP) cflags); \
	ldflags=$$(make -C $(PHP) ldflags); \
	printf '%s\n' \
		"HOME: \"$$HOME\"" \
		"GOPATH: \"$${GOPATH:-$$HOME/go}\"" \
		"GOFLAGS: \"-tags=nowatcher,desktop,dev\"" \
		"CGO_ENABLED: \"1\"" \
		"$$deployment_target" \
		"CGO_CFLAGS: \"$$cflags\"" \
		"CGO_CPPFLAGS: \"$$cflags\"" \
		"CGO_LDFLAGS: \"$$ldflags\"" \
		> $(ROOT)/env.yaml; \
	echo "Generated env.yaml"

.PHONY: clean
clean:
	rm -rf dist/frankenwails

.PHONY: tidy
tidy:
	cd $(ROOT) && go mod tidy
