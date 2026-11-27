#!/usr/bin/env bash
# Crea dist/gb-popup.zip (installabile da Plugin > Aggiungi nuovo > Carica plugin).
# Usato anche dalla GitHub Action delle release.
set -euo pipefail

cd "$(dirname "$0")/.."
ROOT="$(pwd)"
BUILD="$(mktemp -d)"

composer install --no-dev --optimize-autoloader --no-interaction --quiet

mkdir -p "$BUILD/gb-popup" "$ROOT/dist"
rsync -a --exclude-from="$ROOT/.distignore" "$ROOT/" "$BUILD/gb-popup/"

rm -f "$ROOT/dist/gb-popup.zip"
(cd "$BUILD" && zip -rq "$ROOT/dist/gb-popup.zip" gb-popup)
rm -rf "$BUILD"

echo "Creato dist/gb-popup.zip"
