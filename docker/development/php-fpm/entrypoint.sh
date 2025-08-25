#!/bin/sh
set -e

# Check if $UID and $GID are set, else fallback to default (1000:1000)
USER_ID=${UID:-1000}
GROUP_ID=${GID:-1000}

# Fix file ownership and permissions using the passed UID and GID
echo "Fixing file permissions with UID=${USER_ID} and GID=${GROUP_ID}..."
chown -R ${USER_ID}:${GROUP_ID} /var/www || echo "Some files could not be changed"

# Ensure proper permissions for web server access
chmod -R 755 /var/www/storage || echo "Could not set storage permissions"
chmod -R 755 /var/www/bootstrap/cache || echo "Could not set cache permissions"
chmod -R 755 /var/www/public || echo "Could not set public permissions"
chmod 755 /var/www || echo "Could not set www directory permissions"

# Clear configurations to avoid caching issues in development
echo "Clearing configurations..."
cd /var/www
php artisan config:clear || echo "Config clear failed (normal on first run)"
php artisan route:clear || echo "Route clear failed (normal on first run)"
php artisan view:clear || echo "View clear failed (normal on first run)"
php artisan migrate:fresh --seed || echo "Migration/seed failed"

# Create storage link if it doesn't exist
echo "Creating storage link..."
php artisan storage:link || echo "Storage link already exists or failed"

# Run the default command (e.g., php-fpm or bash)
exec "$@"