#!/usr/bin/env bash
# Build or preview the Hugo documentation site via Docker/Podman (no local Hugo install required).
# Usage: ./scripts/invoke-hugo-site.sh [build|serve|preview] [--runtime docker|podman] [--serve-port N] [--preview-port N]

set -euo pipefail

COMMAND="build"
RUNTIME=""
SERVE_PORT=1313
PREVIEW_PORT=8080

usage() {
    cat <<'EOF'
Usage: invoke-hugo-site.sh [build|serve|preview] [options]

Commands:
  build    Production-parity build to website/public/ (default)
  serve    Hugo dev server with live reload
  preview  Local static preview via nginx (overrides baseURL for localhost)

Options:
  --runtime RUNTIME       Container runtime: docker or podman (auto-detected if omitted)
  --serve-port PORT       Port for serve command (default: 1313)
  --preview-port PORT     Port for preview command (default: 8080)
  -h, --help              Show this help

Examples:
  ./scripts/invoke-hugo-site.sh preview
  ./scripts/invoke-hugo-site.sh serve --runtime docker
  ./scripts/invoke-hugo-site.sh build
EOF
}

while [[ $# -gt 0 ]]; do
    case "$1" in
        build|serve|preview)
            COMMAND="$1"
            shift
            ;;
        --runtime)
            RUNTIME="${2:-}"
            shift 2
            ;;
        --serve-port)
            SERVE_PORT="${2:-}"
            shift 2
            ;;
        --preview-port)
            PREVIEW_PORT="${2:-}"
            shift 2
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            echo "error: unknown argument '$1'" >&2
            usage >&2
            exit 1
            ;;
    esac
done

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
SITE_DIR="$REPO_ROOT/website"
PUBLIC_PATH="$SITE_DIR/public"
HUGO_IMAGE="docker.io/hugomods/hugo:latest"
NGINX_IMAGE="docker.io/library/nginx:alpine"

resolve_runtime() {
    if [[ -n "$RUNTIME" ]]; then
        if ! command -v "$RUNTIME" >/dev/null 2>&1; then
            echo "error: container runtime '$RUNTIME' not found on PATH" >&2
            exit 1
        fi
        return
    fi

    if command -v docker >/dev/null 2>&1; then
        RUNTIME="docker"
    elif command -v podman >/dev/null 2>&1; then
        RUNTIME="podman"
    else
        echo "error: no container runtime found. Install Docker or Podman, or pass --runtime." >&2
        exit 1
    fi
}

volume_suffix() {
    if [[ "$RUNTIME" == "podman" ]]; then
        printf ':Z'
    fi
}

hugo_build() {
    local base_url="${1:-}"
    local -a hugo_args=(--minify)

    if [[ -n "$base_url" ]]; then
        hugo_args+=(--baseURL "$base_url")
    fi

    "$RUNTIME" run --rm \
        -v "${REPO_ROOT}:/src$(volume_suffix)" \
        -w /src/website \
        "$HUGO_IMAGE" \
        hugo "${hugo_args[@]}"

    if [[ ! -f "$PUBLIC_PATH/index.html" ]]; then
        echo "error: Hugo did not produce website/public/index.html" >&2
        exit 1
    fi
}

resolve_runtime

case "$COMMAND" in
    build)
        echo "Building Hugo site to website/public..."
        hugo_build
        echo "Done. Output: $PUBLIC_PATH"
        ;;
    serve)
        echo "Starting Hugo dev server at http://localhost:${SERVE_PORT} ..."
        "$RUNTIME" run --rm -p "${SERVE_PORT}:1313" \
            -v "${REPO_ROOT}:/src$(volume_suffix)" \
            -w /src/website \
            "$HUGO_IMAGE" \
            hugo server --bind 0.0.0.0 --baseURL "http://localhost:${SERVE_PORT}"
        ;;
    preview)
        echo "Building Hugo site..."
        hugo_build "http://localhost:${PREVIEW_PORT}/"
        echo "Serving website/public at http://localhost:${PREVIEW_PORT} ..."
        "$RUNTIME" run --rm -p "${PREVIEW_PORT}:80" \
            -v "${REPO_ROOT}/website/public:/usr/share/nginx/html:ro$(volume_suffix)" \
            "$NGINX_IMAGE"
        ;;
esac
