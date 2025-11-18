#!/bin/sh

# Health check script for Laravel application

# Check if php-fpm is running
if ! pgrep -f php-fpm >/dev/null 2>&1; then
    echo "PHP-FPM is not running"
    exit 1
fi

# Check if we can connect to the database
if ! php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB OK';" >/dev/null 2>&1; then
    echo "Database connection failed"
    exit 1
fi

# Check if Redis is accessible
if ! php artisan tinker --execute="Redis::ping(); echo 'Redis OK';" >/dev/null 2>&1; then
    echo "Redis connection failed"
    exit 1
fi

echo "All health checks passed"
exit 0