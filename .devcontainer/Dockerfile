FROM mcr.microsoft.com/devcontainers/php:1-8.3

# The base image includes a Yarn apt source that can fail key verification in
# feature install steps. Remove it so apt update used by features can proceed.
RUN rm -f /etc/apt/sources.list.d/yarn.list

# Copilot-likes currently installs `git-delta` via apt, but that package is
# not available in Debian bookworm. Preinstall the upstream .deb so the
# feature's package install step can resolve successfully.
ARG DELTA_VERSION=0.18.2
RUN set -eux; \
	arch="$(dpkg --print-architecture)"; \
	case "$arch" in \
		amd64) delta_arch="amd64" ;; \
		arm64) delta_arch="arm64" ;; \
		*) echo "Skipping git-delta preinstall for unsupported arch: $arch"; exit 0 ;; \
	esac; \
	curl -fsSL -o /tmp/git-delta.deb "https://github.com/dandavison/delta/releases/download/${DELTA_VERSION}/git-delta_${DELTA_VERSION}_${delta_arch}.deb"; \
	dpkg -i /tmp/git-delta.deb; \
	rm -f /tmp/git-delta.deb
