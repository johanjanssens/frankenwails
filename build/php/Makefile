export MAKEFLAGS='--silent --environment-override'

ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST)))/../..)
OS   := $(shell uname -s | tr '[:upper:]' '[:lower:]')
ARCH := $(shell uname -m)

ifneq (,$(findstring $(OS),CYGWIN_NT MINGW32_NT MSYS_NT MINGW64_NT))
    OS := windows
endif

ifeq ($(OS),darwin)
    OS := macos
endif

# Default variables
clean   ?= ""
debug   ?= ""
nostrip ?= ""

php_dir  ?= $(ROOT)/dist/.php

PHP_EXTENSIONS     ?= "bcmath,bz2,calendar,ctype,curl,dom,exif,fileinfo,filter,gd,gmp,iconv,intl,mbregex,mbstring,opcache,openssl,pdo,pdo_sqlite,phar,posix,readline,session,simplexml,sqlite3,swow,tidy,tokenizer,xml,xmlreader,xmlwriter,zip,zlib,yaml,zstd"
PHP_EXTENSION_LIBS ?= "bzip2,freetype,libjpeg,libwebp,libzip,nghttp2"
PHP_VERSION        ?= "8.3"

.ONESHELL:

.PHONY: all
all: clean download build

.PHONY: clean
clean:
	if [ -n "$(clean)" ]; then
		echo "Cleaning cache..."
		rm -rf $(php_dir)/downloads
		rm -rf $(php_dir)/buildroot
		rm -rf $(php_dir)/sources
	fi

.PHONY: download
download:
	@download_spc() {
		case "$(OS)" in
			linux)
			case "$(ARCH)" in
				x86_64) url="spc-linux-x86_64" ;;
				aarch64) url="spc-linux-aarch64" ;;
				*) echo "Unsupported architecture: $(ARCH)"; exit 1 ;;
			esac ;;
			macos)
			case "$(ARCH)" in
				x86_64) url="spc-macos-x86_64" ;;
				arm64) url="spc-macos-aarch64" ;;
				*) echo "Unsupported architecture: $(ARCH)"; exit 1 ;;
			esac ;;
			windows)
			case "$(ARCH)" in
				x86_64) url="spc-windows-x64.exe" ;;
				*) echo "Unsupported architecture: $(ARCH)"; exit 1 ;;
			esac ;;
			*) echo "Unsupported operating system: $(OS)"; exit 1 ;;
		esac

		url="https://dl.static-php.dev/static-php-cli/spc-bin/nightly/$$url"
		echo "Downloading from $$url..."
		curl -fsSL -o ./spc "$$url"

		if [ "$(OS)" = "linux" ] || [ "$(OS)" = "macos" ]; then
			chmod +x ./spc
			version=$$(./spc --version)
			echo "$$version installed"
		fi
	}

	mkdir -p $(php_dir)
	cd $(php_dir)
	if [ ! -f "spc" ] || [ -n "$(clean)" ]; then
		download_spc
	fi

.PHONY: build
build:

	cd $(php_dir)

	if [ "$(OS)" = "macos" ]; then
		export MACOSX_DEPLOYMENT_TARGET="15.0"
	fi

	extraOpts=""
	if [ "$(OS)" = "linux" ]; then
		extraOpts="--disable-opcache-jit";
	fi

	if [ -n "$(nostrip)" ]; then
		extraOpts="$$extraOpts --no-strip";
	fi

	if [ -n "$(debug)" ]; then
		extraOpts="$$extraOpts --debug";
	fi

	mkdir -p $(php_dir)
	cd $(php_dir)
	if [ ! -f buildroot/lib/libphp.a ]; then
		echo "Building PHP $(PHP_VERSION)..."
		./spc doctor --auto-fix
		./spc download --with-php=$(PHP_VERSION) --for-extensions="$(PHP_EXTENSIONS)" --for-libs="$(PHP_EXTENSION_LIBS)" --ignore-cache-sources=php-src --prefer-pre-built
		./spc build --enable-zts --build-embed $$extraOpts "$(PHP_EXTENSIONS)" --with-libs="$(PHP_EXTENSION_LIBS)"
	fi

.PHONY: cflags
cflags:

	cd $(php_dir)
	cflags=$$(./spc spc-config "$(PHP_EXTENSIONS)" --with-libs="$(PHP_EXTENSION_LIBS)" --includes)
	cflags="$${cflags} -I${php_dir}/downloads/swow/ext/include  \
		-I${php_dir}/downloads/swow/ext/deps/libcat/include  \
		-I${php_dir}/downloads/swow/ext/deps/libcat/deps/libuv/include \
		-I${php_dir}/downloads/swow/ext/deps/libcat/deps/llhttp/include"

	if [ "$(OS)" = "linux" ] && { [[ "$(ARCH)" =~ "aarch" ]] || [[ "$(ARCH)" =~ "arm" ]]; }; then
		fpic="-fPIC"
		fpie="-fPIE"
	else
		fpic="-fpic"
		fpie="-fpie"
	fi

	if [ -n "$(debug)" ]; then
		cflags="-g $${cflags}"
	else
		cflags="-fstack-protector-strong ${fpic} ${fpie} -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64 $${cflags}"
	fi

	echo "$${cflags}"

.PHONY: ldflags
ldflags:
	cd $(php_dir)
	ldflags=$$(./spc spc-config "$(PHP_EXTENSIONS)" --with-libs="$(PHP_EXTENSION_LIBS)" --libs)

	if [ "$(OS)" = "linux" ] && [ -z "${debug}" ]; then
		ldflags="-Wl,-O1 -pie $${ldflags}"
	fi

	if [ "$(OS)" = "macos" ]; then
		ldflags="-L/opt/homebrew/opt/sqlite/lib $${ldflags} -framework UniformTypeIdentifiers "
	fi

	if [ "$(OS)" = "linux" ]; then
		if echo "$(PHP_EXTENSIONS)" | grep -qE "\b(intl|imagick|grpc|v8js|protobuf|mongodb|tbb)\b"; then
			ldflags="$${ldflags} -lstdc++"
		fi
	fi

	echo "$${ldflags}"

.PHONY: print
print:
	echo "PHP_VERSION=$$($(php_dir)/buildroot/bin/php-config --version)"
	echo "CGO_CFLAGS=$$(make cflags)"
	echo "CGO_LDFLAGS=$$(make ldflags)"

.PHONY: version
version:
	$(php_dir)/buildroot/bin/php-config --version
