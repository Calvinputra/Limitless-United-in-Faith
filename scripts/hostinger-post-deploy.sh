#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

if [ -f composer.json ]; then
  composer install --no-dev --optimize-autoloader --no-interaction
fi

if [ -f artisan ]; then
  php artisan migrate --force
  php artisan storage:link || true

  # Fallback when symlink is blocked on shared hosting
  if [ ! -e "public/storage" ] && [ -d "storage/app/public" ]; then
    ln -sfn ../storage/app/public public/storage 2>/dev/null || true
  fi

  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
fi

echo "Hostinger post-deploy finished."
