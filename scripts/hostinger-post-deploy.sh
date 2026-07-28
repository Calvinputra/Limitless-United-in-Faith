#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

if [ -f composer.json ]; then
  composer install --no-dev --optimize-autoloader --no-interaction
fi

if [ -f artisan ]; then
  php artisan migrate --force
  php artisan storage:link || true
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
fi

echo "Hostinger post-deploy finished."
