#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT_DIR="${ROOT_DIR}/dist"
NAME="perfex-api-docs-$(date +%Y%m%d-%H%M%S)"

mkdir -p "$OUT_DIR"
cd "$ROOT_DIR"
zip -qr "${OUT_DIR}/${NAME}.zip" . \
  -x '.git/*' '.venv/*' '.cache/*' 'dist/*' '__pycache__/*' '*.pyc'
printf 'Created %s\n' "${OUT_DIR}/${NAME}.zip"
