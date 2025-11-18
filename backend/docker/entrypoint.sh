#!/bin/sh
set -e

echo "Starting Laravel application setup..."

# Install composer dependencies at runtime
echo "Installing composer dependencies..."
composer install --optimize-autoloader --no-interaction

# Generate application key
echo "Generating application key..."
php artisan key:generate --force

# Wait for database
echo "Waiting for database..."
until php -r "try { new PDO('mysql:host=mysql;port=3306;dbname=metal_exchange', 'metal', 'metal'); } catch(Exception \$e) { exit(1); }" 2>/dev/null; do
    sleep 2
done

echo "Running migrations..."
php artisan migrate --force

echo "Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "Starting PHP-FPM..."
exec "$@"
