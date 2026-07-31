#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

if [ -f composer.json ]; then
  composer install --no-dev --optimize-autoloader --no-interaction
fi

# Re-bind uploads to persistent folder OUTSIDE public_html (survives Git deploy).
if [ -f scripts/hostinger-setup-persistent-storage.sh ]; then
  bash scripts/hostinger-setup-persistent-storage.sh
fi

if [ -f artisan ]; then
  php artisan migrate --force
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
fi

echo "Hostinger post-deploy finished."
