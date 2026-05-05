#!/bin/sh
set -e

cd /var/www/html

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    /var/lib/nginx/tmp/client_body \
    /var/lib/nginx/tmp/proxy \
    /var/lib/nginx/tmp/fastcgi \
    /var/lib/nginx/tmp/uwsgi \
    /var/lib/nginx/tmp/scgi \
    /var/log/nginx \
    /var/run

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwx storage bootstrap/cache || true
chown -R nginx:nginx /var/lib/nginx /var/log/nginx /var/run || true

php artisan optimize:clear || true

# Recreate the public/storage symlink inside Linux containers.
# A copied Windows junction can arrive here as a real directory and break /storage URLs.
if [ -e public/storage ] && [ ! -L public/storage ]; then
    rm -rf public/storage
fi

if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

until php artisan migrate --force; do
    echo "Database belum siap, ulangi migrate dalam 5 detik..."
    sleep 5
done

exec /usr/bin/supervisord -c /etc/supervisord.conf
