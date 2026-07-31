#!/usr/bin/env bash
# One-time (or re-run after deploy) — keep bukti TF outside public_html so Git deploy cannot wipe it.
set -euo pipefail

APP_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SITE_ROOT="$(dirname "$APP_ROOT")"
PERSISTENT_PUBLIC="${PUBLIC_STORAGE_PATH:-$SITE_ROOT/persistent/app/public}"

mkdir -p "$PERSISTENT_PUBLIC/bukti-tf"
chmod -R u+rwX "$SITE_ROOT/persistent" 2>/dev/null || true

ENV_FILE="$APP_ROOT/.env"
if [ ! -f "$ENV_FILE" ]; then
  echo "ERROR: $ENV_FILE tidak ditemukan."
  exit 1
fi

if grep -q '^PUBLIC_STORAGE_PATH=' "$ENV_FILE"; then
  sed -i.bak "s|^PUBLIC_STORAGE_PATH=.*|PUBLIC_STORAGE_PATH=$PERSISTENT_PUBLIC|" "$ENV_FILE"
  rm -f "$ENV_FILE.bak"
else
  printf '\nPUBLIC_STORAGE_PATH=%s\n' "$PERSISTENT_PUBLIC" >> "$ENV_FILE"
fi

# Keep Laravel's storage/app/public pointing at the persistent folder when possible.
if [ -L "$APP_ROOT/storage/app/public" ] || [ ! -e "$APP_ROOT/storage/app/public" ]; then
  ln -sfn "$PERSISTENT_PUBLIC" "$APP_ROOT/storage/app/public"
elif [ -d "$APP_ROOT/storage/app/public" ] && [ ! -L "$APP_ROOT/storage/app/public" ]; then
  # Move any leftover uploads into persistent storage, then replace with symlink.
  shopt -s nullglob dotglob
  leftovers=("$APP_ROOT"/storage/app/public/*)
  if [ ${#leftovers[@]} -gt 0 ]; then
    cp -a "$APP_ROOT"/storage/app/public/. "$PERSISTENT_PUBLIC"/
  fi
  shopt -u nullglob dotglob
  rm -rf "$APP_ROOT/storage/app/public"
  ln -sfn "$PERSISTENT_PUBLIC" "$APP_ROOT/storage/app/public"
fi

# Optional public URL symlink (app serves bukti via auth route; this is fallback).
mkdir -p "$APP_ROOT/public"
if [ -L "$APP_ROOT/public/storage" ] || [ ! -e "$APP_ROOT/public/storage" ]; then
  ln -sfn "$PERSISTENT_PUBLIC" "$APP_ROOT/public/storage" 2>/dev/null || true
fi

cd "$APP_ROOT"
php artisan config:clear
php artisan storage:link 2>/dev/null || true

echo ""
echo "Persistent storage siap:"
echo "  $PERSISTENT_PUBLIC"
echo "  bukti-tf files: $(find "$PERSISTENT_PUBLIC/bukti-tf" -type f 2>/dev/null | wc -l | tr -d ' ')"
echo ""
echo "Deploy Git tidak akan menghapus folder ini (di luar public_html)."
