#!/bin/bash
# ==============================================================================
# DilSe Match Dating Platform - Automated Pull & Deployment Script
# Domain: taskbazi.site
# Repository: https://github.com/wbsoumo/Dating-app.git
# ==============================================================================

set -e

echo "🚀 [$(date '+%Y-%m-%d %H:%M:%S')] Starting automated pull & deployment..."

# Navigate to project root directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "📂 Project Directory: $SCRIPT_DIR"

# 1. Pull latest code from GitHub main branch
echo "⬇️  Pulling latest commits from origin main..."
git fetch origin main
git reset --hard origin/main

# 2. Check if .env exists, if not copy production template
if [ ! -f .env ]; then
    echo "⚙️  .env not found. Creating from .env.taskbazi..."
    if [ -f .env.taskbazi ]; then
        cp .env.taskbazi .env
    else
        cp .env.example .env
    fi
    php artisan key:generate --force
fi

# 3. Install/Update Composer Dependencies (if composer is available)
if command -v composer &> /dev/null; then
    echo "📦 Updating Composer dependencies..."
    composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
else
    echo "ℹ️  Composer binary not found in PATH, skipping composer install."
fi

# 4. Run pending database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# 5. Clear and rebuild application caches
echo "🧹 Optimizing application caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Ensure directory permissions are correct for shared hosting
echo "🔒 Setting storage directory permissions..."
chmod -R 775 storage bootstrap/cache public/uploads 2>/dev/null || true

echo "✅ [$(date '+%Y-%m-%d %H:%M:%S')] Deployment completed successfully!"
echo "🌐 Platform live at: https://taskbazi.site"
