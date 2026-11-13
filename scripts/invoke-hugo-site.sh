#!/usr/bin/env bash
# Build or preview the Hugo documentation site via Docker/Podman (no local Hugo install required).
# Usage: ./scripts/invoke-hugo-site.sh [build|serve|preview] [--runtime docker|podman] [--serve-port N] [--preview-port N]

set -euo pipefail

COMMAND="build"
RUNTIME=""
SERVE_PORT=1313
# 1314 avoids clashing with WordPress smoke (WORDPRESS_HTTP_PORT defaults to 8080).
PREVIEW_PORT="${MONOLOG_HUGO_PREVIEW_PORT:-1314}"

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
  --preview-port PORT     Port for preview command (default: 1314; env MONOLOG_HUGO_PREVIEW_PORT)
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

tcp_port_taken() {
    local port="$1"

    if command -v ss >/dev/null 2>&1; then
        if ss -H -tln 2>/dev/null | grep -qE ":${port}([[:space:]]|$)"; then
            return 0
        fi
    fi

    if [[ -n "${RUNTIME:-}" ]] && command -v "$RUNTIME" >/dev/null 2>&1; then
        if "$RUNTIME" ps --format '{{.Ports}}' 2>/dev/null | grep -qE "(0\\.0\\.0\\.0|:::|\\[::\\]):${port}->"; then
            return 0
        fi
    fi

    return 1
}

# Pick the requested port, or the next free port if something else (e.g. WordPress smoke) is bound.
resolve_listen_port() {
    local var_name="$1"
    local -n port_ref="$var_name"
    local requested="$port_ref"
    local port="$requested"
    local attempts=0
    local max_attempts=25

    while tcp_port_taken "$port" && (( attempts < max_attempts )); do
        if (( attempts == 0 )); then
            echo "note: port ${port} is already in use; trying the next available port" >&2
            if [[ "$port" -eq 8080 ]]; then
                echo "      (8080 is the default WordPress smoke port — composer run wp:env:down frees it.)" >&2
            fi
        fi
        port=$((port + 1))
        attempts=$((attempts + 1))
    done

    if tcp_port_taken "$port"; then
        echo "error: no free TCP port found near ${requested}" >&2
        exit 1
    fi

    if (( port != requested )); then
        echo "note: using port ${port} (requested ${requested})" >&2
    fi

    port_ref="$port"
}

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

volume_mount() {
    local host_path="$1"
    local container_path="$2"
    local opts="${3:-}"

    if [[ "$RUNTIME" == "podman" ]]; then
        if [[ -n "$opts" ]]; then
            opts="${opts},Z"
        else
            opts="Z"
        fi
    fi

    if [[ -n "$opts" ]]; then
        printf '%s:%s:%s' "$host_path" "$container_path" "$opts"
    else
        printf '%s:%s' "$host_path" "$container_path"
    fi
}

# Docker-outside-of-Docker (Dev Containers / Codespaces) runs the host daemon, so bind
# mounts use host paths. When the workspace only exists inside the dev container, mounts
# look empty to nested containers — the symptom is Hugo's "Unable to locate config file".
docker_bind_mount_works() {
    local host_path="$1"
    local workdir="$2"
    local test_file="$3"

    "$RUNTIME" run --rm \
        -v "$(volume_mount "$host_path" /src)" \
        -w "$workdir" \
        "$HUGO_IMAGE" \
        test -f "$test_file"
}

hugo_extended_available() {
    command -v hugo >/dev/null 2>&1 && hugo version 2>/dev/null | grep -qi extended
}

assert_public_index() {
    if [[ ! -f "$PUBLIC_PATH/index.html" ]]; then
        echo "error: Hugo did not produce website/public/index.html" >&2
        exit 1
    fi
}

hugo_build_bind_mount() {
    local base_url="${1:-}"
    local -a hugo_args=(--minify)

    if [[ -n "$base_url" ]]; then
        hugo_args+=(--baseURL "$base_url")
    fi

    "$RUNTIME" run --rm \
        -v "$(volume_mount "$REPO_ROOT" /src)" \
        -w /src/website \
        "$HUGO_IMAGE" \
        hugo "${hugo_args[@]}"
}

hugo_build_local() {
    local base_url="${1:-}"
    local -a hugo_args=(--minify)

    if [[ -n "$base_url" ]]; then
        hugo_args+=(--baseURL "$base_url")
    fi

    (
        cd "$SITE_DIR"
        hugo "${hugo_args[@]}"
    )
}

hugo_build_streamed() {
    local base_url="${1:-}"
    local container_name="monolog-hugo-build-$$"
    local -a docker_env=()
    local hugo_cmd="hugo --minify"

    if [[ -n "$base_url" ]]; then
        docker_env+=(-e "HUGO_BASE_URL=$base_url")
        hugo_cmd='hugo --minify --baseURL "$HUGO_BASE_URL"'
    fi

    echo "note: using docker cp build (bind mounts unavailable on this Docker host)" >&2

    rm -rf "$PUBLIC_PATH"

    "$RUNTIME" create "${docker_env[@]}" --name "$container_name" -w /site \
        --entrypoint sh "$HUGO_IMAGE" -c "cd /site && $hugo_cmd"
    "$RUNTIME" cp "$SITE_DIR/." "$container_name:/site"
    if ! "$RUNTIME" start -a "$container_name"; then
        "$RUNTIME" rm -f "$container_name" >/dev/null 2>&1 || true
        return 1
    fi
    "$RUNTIME" cp "$container_name:/site/public/." "$PUBLIC_PATH"
    "$RUNTIME" rm -f "$container_name" >/dev/null 2>&1 || true
}

hugo_build() {
    local base_url="${1:-}"

    if docker_bind_mount_works "$REPO_ROOT" /src/website hugo.yaml; then
        hugo_build_bind_mount "$base_url"
    elif hugo_extended_available; then
        hugo_build_local "$base_url"
    else
        hugo_build_streamed "$base_url"
    fi

    assert_public_index
}

serve_static_preview() {
    if docker_bind_mount_works "$REPO_ROOT/website/public" /usr/share/nginx/html index.html; then
        "$RUNTIME" run --rm -p "${PREVIEW_PORT}:80" \
            -v "$(volume_mount "$REPO_ROOT/website/public" /usr/share/nginx/html ro)" \
            "$NGINX_IMAGE"
        return
    fi

    local container_name="monolog-hugo-preview-$$"

    echo "note: serving preview via docker cp (bind mounts unavailable on this Docker host)" >&2
    "$RUNTIME" create --name "$container_name" -p "${PREVIEW_PORT}:80" \
        --entrypoint sh "$NGINX_IMAGE" -c 'exec nginx -g "daemon off;"'
    "$RUNTIME" cp "$PUBLIC_PATH/." "$container_name:/usr/share/nginx/html"
    if ! "$RUNTIME" start -a "$container_name"; then
        "$RUNTIME" rm -f "$container_name" >/dev/null 2>&1 || true
        return 1
    fi
    "$RUNTIME" rm -f "$container_name" >/dev/null 2>&1 || true
}

hugo_serve() {
    if docker_bind_mount_works "$REPO_ROOT" /src/website hugo.yaml; then
        "$RUNTIME" run --rm -p "${SERVE_PORT}:1313" \
            -v "$(volume_mount "$REPO_ROOT" /src)" \
            -w /src/website \
            "$HUGO_IMAGE" \
            hugo server --bind 0.0.0.0 --baseURL "http://localhost:${SERVE_PORT}"
        return
    fi

    if hugo_extended_available; then
        (
            cd "$SITE_DIR"
            exec hugo server --bind 0.0.0.0 --baseURL "http://localhost:${SERVE_PORT}"
        )
        return
    fi

    cat <<EOF >&2
error: cannot start Hugo live reload in this environment.

Bind mounts from the dev container workspace are not visible to the host Docker daemon
(Docker-outside-of-Docker). Install Hugo Extended locally, or use:

  ./scripts/invoke-hugo-site.sh preview

for a static build and browser preview.
EOF
    exit 1
}

resolve_runtime

case "$COMMAND" in
    build)
        echo "Building Hugo site to website/public..."
        hugo_build
        echo "Done. Output: $PUBLIC_PATH"
        ;;
    serve)
        resolve_listen_port SERVE_PORT
        echo "Starting Hugo dev server at http://localhost:${SERVE_PORT} ..."
        hugo_serve
        ;;
    preview)
        resolve_listen_port PREVIEW_PORT
        echo "Building Hugo site..."
        hugo_build "http://localhost:${PREVIEW_PORT}/"
        echo "Serving website/public at http://localhost:${PREVIEW_PORT} ..."
        serve_static_preview
        ;;
esac
