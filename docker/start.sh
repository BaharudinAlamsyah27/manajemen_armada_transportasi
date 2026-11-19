#!/usr/bin/env bash
set -e

PORT_TO_USE="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT_TO_USE}/g" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT_TO_USE}>/g" /etc/apache2/sites-available/000-default.conf

# Pastikan file SQLite tersedia bila memakai koneksi sqlite
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    mkdir -p /var/www/html/database
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
fi

# Jalankan migrasi & seed otomatis hanya jika tabel users masih kosong
php artisan migrate --force
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tr -dc '0-9' || echo "0")
if [ "${USER_COUNT:-0}" = "0" ]; then
    php artisan db:seed --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

exec apache2-foreground
