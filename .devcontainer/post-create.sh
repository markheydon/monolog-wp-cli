#!/usr/bin/env bash
set -euo pipefail

composer install --prefer-dist --no-progress --no-interaction
composer validate --strict

if ! command -v rsync >/dev/null 2>&1; then
	sudo apt-get update
	sudo DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends rsync
fi

composer run qa
