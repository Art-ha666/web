#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

echo "[entrypoint] Booting AKH Solutions container…"

# Discover packages then cache config/routes/views/events for production speed.
php artisan package:discover --ansi || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

# Storage symlink for any uploaded media.
php artisan storage:link 2>/dev/null || true

# Run migrations on boot only when explicitly enabled. In a multi-task
# deployment, prefer running migrations once as a dedicated ECS task
# (see .github/workflows/deploy.yml) and keep RUN_MIGRATIONS=false here.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "[entrypoint] Running migrations…"
    php artisan migrate --force
fi

echo "[entrypoint] Ready."
exec "$@"
