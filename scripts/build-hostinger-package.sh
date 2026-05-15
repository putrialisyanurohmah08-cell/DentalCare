#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DEPLOY_DIR="$ROOT_DIR/deploy/hostinger"
STAGE_DIR="$DEPLOY_DIR/stage"
APP_DIR="$STAGE_DIR/dentalcare_app"
PUBLIC_DIR="$STAGE_DIR/public_html"
DATABASE_DIR="$DEPLOY_DIR/database"

cd "$ROOT_DIR"

echo "Preparing production dependencies..."
composer install --no-dev --prefer-dist --optimize-autoloader

echo "Building frontend assets..."
if [ ! -x node_modules/.bin/vite ]; then
    if [ -d node_modules ] && [ ! -w node_modules ]; then
        mv node_modules "node_modules.unwritable.$(date +%s)"
    fi
    npm ci
fi
npm run build

echo "Creating staging folders..."
rm -rf "$DEPLOY_DIR"
mkdir -p "$APP_DIR" "$PUBLIC_DIR" "$DATABASE_DIR"

echo "Copying Laravel application..."
rsync -a ./ "$APP_DIR/" \
    --exclude='.git' \
    --exclude='.codex' \
    --exclude='.env' \
    --exclude='.env.backup' \
    --exclude='.env.production' \
    --exclude='.env.hostinger' \
    --exclude='node_modules' \
    --exclude='node_modules.*' \
    --exclude='deploy' \
    --exclude='docker' \
    --exclude='Dockerfile' \
    --exclude='docker-compose.yml' \
    --exclude='playwright-temp' \
    --exclude='test-results' \
    --exclude='tests' \
    --exclude='storage/logs/*.log' \
    --exclude='storage/framework/views/*.php'

echo "Copying public web root..."
rsync -a public/ "$PUBLIC_DIR/"

cat > "$PUBLIC_DIR/index.php" <<'PHP'
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$appPath = __DIR__.'/../dentalcare_app';

if (file_exists($maintenance = $appPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appPath.'/vendor/autoload.php';

(require_once $appPath.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
PHP

echo "Preparing writable Laravel folders..."
mkdir -p "$APP_DIR/storage/app/private" \
    "$APP_DIR/storage/app/public" \
    "$APP_DIR/storage/framework/cache/data" \
    "$APP_DIR/storage/framework/sessions" \
    "$APP_DIR/storage/framework/testing" \
    "$APP_DIR/storage/framework/views" \
    "$APP_DIR/storage/logs" \
    "$APP_DIR/bootstrap/cache"

find "$APP_DIR/storage" -type d -exec chmod 775 {} +
find "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} +

if [ -f "$ROOT_DIR/dentalcare.sql" ]; then
    cp "$ROOT_DIR/dentalcare.sql" "$DATABASE_DIR/dentalcare.sql"
fi

echo "Creating ZIP files..."
(
    cd "$STAGE_DIR/dentalcare_app"
    zip -qr "$DEPLOY_DIR/dentalcare_app.zip" .
)
(
    cd "$STAGE_DIR/public_html"
    zip -qr "$DEPLOY_DIR/public_html.zip" .
)

if [ -f "$DATABASE_DIR/dentalcare.sql" ]; then
    (
        cd "$DATABASE_DIR"
        zip -qr "$DEPLOY_DIR/dentalcare_database.zip" dentalcare.sql
    )
fi

echo "Done."
echo "Generated:"
echo "  $DEPLOY_DIR/dentalcare_app.zip"
echo "  $DEPLOY_DIR/public_html.zip"
if [ -f "$DEPLOY_DIR/dentalcare_database.zip" ]; then
    echo "  $DEPLOY_DIR/dentalcare_database.zip"
fi
