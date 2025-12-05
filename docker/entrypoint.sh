#!/bin/bash
set -e

# =============================================================================
# FrankenPHP Docker Entrypoint Script
# =============================================================================

echo "🚀 Starting Laravel Application with FrankenPHP..."

# Wait for database to be ready (if using MySQL)
if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" = "mysql" ]; then
    echo "⏳ Waiting for MySQL to be ready..."
    while ! nc -z "$DB_HOST" "${DB_PORT:-3306}" 2>/dev/null; do
        sleep 1
    done
    echo "✅ MySQL is ready!"
fi

# Wait for Redis if configured
if [ -n "$REDIS_HOST" ]; then
    echo "⏳ Waiting for Redis to be ready..."
    while ! nc -z "$REDIS_HOST" "${REDIS_PORT:-6379}" 2>/dev/null; do
        sleep 1
    done
    echo "✅ Redis is ready!"
fi

# Laravel optimizations for production
if [ "$APP_ENV" = "production" ]; then
    echo "🔧 Running production optimizations..."
    
    # Clear and cache config
    php artisan config:cache
    
    # Cache routes
    php artisan route:cache
    
    # Cache views
    php artisan view:cache
    
    # Optimize autoloader
    php artisan optimize
    
    echo "✅ Production optimizations complete!"
fi

# Run migrations if enabled
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "🗃️ Running database migrations..."
    php artisan migrate --force
    echo "✅ Migrations complete!"
fi

# Link storage if not already linked
if [ ! -L "public/storage" ]; then
    echo "🔗 Creating storage link..."
    php artisan storage:link 2>/dev/null || true
fi

echo "✅ Laravel is ready!"
echo "🧟 Starting FrankenPHP..."

# Execute the main command
exec "$@"
