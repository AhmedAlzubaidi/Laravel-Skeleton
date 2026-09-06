#!/bin/sh
set -e

USER_ID=${UID:-1000}
GROUP_ID=${GID:-1000}

echo "Fixing file permissions with UID=${USER_ID} and GID=${GROUP_ID}..."
chown -R ${USER_ID}:${GROUP_ID} /var/www || echo "Some files could not be changed"

chmod -R 755 /var/www/storage || echo "Could not set storage permissions"
# The chmod above also widens the OAuth keys, which Passport then refuses to load.
# Narrow them again here, before php-fpm is exec'd at the end of this script.
chmod 600 /var/www/storage/*.key 2>/dev/null || true
chmod -R 755 /var/www/bootstrap/cache || echo "Could not set cache permissions"
chmod -R 755 /var/www/public || echo "Could not set public permissions"
chmod 755 /var/www || echo "Could not set www directory permissions"

echo "Clearing configurations..."
cd /var/www
php artisan config:clear || echo "Config clear failed (normal on first run)"
php artisan route:clear || echo "Route clear failed (normal on first run)"
php artisan view:clear || echo "View clear failed (normal on first run)"
php artisan migrate || echo "Migration failed"

echo "Creating storage link..."
php artisan storage:link || echo "Storage link already exists or failed"

exec "$@"