#!/bin/sh
set -e

# storage/ is a volume in production, so the image ships a seed copy to restore from
if [ ! "$(ls -A /var/www/storage)" ]; then
  echo "Initializing storage directory..."
  cp -R /var/www/storage-init/. /var/www/storage
  chown -R www-data:www-data /var/www/storage
fi

rm -rf /var/www/storage-init

php artisan migrate --force

php artisan config:cache
php artisan route:cache

exec "$@"
