#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

MODE="upstream"
case "${1:-}" in
  "") ;;
  --custom-only) MODE="custom" ;;
  --crm) MODE="crm" ;;
  *)
    printf 'Usage: %s [--custom-only|--crm]\n' "$0" >&2
    exit 2
    ;;
esac

make install

case "$MODE" in
  upstream)
    make bootstrap
    ;;
  crm)
    if [[ -f .env ]]; then
      set -a
      # shellcheck disable=SC1091
      source .env
      set +a
    fi
    : "${PERFEX_BASE_URL:?Set PERFEX_BASE_URL in the environment or .env}"
    : "${PERFEX_API_TOKEN:?Set PERFEX_API_TOKEN in the environment or .env}"
    make bootstrap-crm
    ;;
  custom)
    printf '%s\n' 'Skipping upstream import; building the bundled custom example only.'
    ;;
esac

make build
printf '\nBuild complete: %s/site\n' "$ROOT_DIR"
printf '%s\n' 'Preview with: make serve'
