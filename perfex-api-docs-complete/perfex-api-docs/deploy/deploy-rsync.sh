#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ -f .env ]]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

: "${DEPLOY_HOST:?Set DEPLOY_HOST in the environment or .env}"
: "${DEPLOY_USER:?Set DEPLOY_USER in the environment or .env}"
: "${DEPLOY_PATH:?Set DEPLOY_PATH in the environment or .env}"

make build
rsync -az --delete site/ "${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}/"
printf 'Deployed site/ to %s@%s:%s/\n' "$DEPLOY_USER" "$DEPLOY_HOST" "$DEPLOY_PATH"
