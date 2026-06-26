#!/usr/bin/env bash
set -e

# Render injects the port the container must listen on. Default to 80 locally.
PORT="${PORT:-80}"
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Generate an application key on the fly if one was not provided.
if [ -z "${APP_KEY}" ]; then
    echo "APP_KEY not set — generating an ephemeral key."
    export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
fi

# Prepare the application.
php artisan config:clear
php artisan migrate --force || echo "Migration step skipped/failed (continuing)."
php artisan storage:link || true

# Seed demo data on first boot (the seeder is idempotent, so this is a
# no-op once the data already exists).
php artisan db:seed --force || true

# Cache configuration & routes for performance in production.
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec apache2-foreground
