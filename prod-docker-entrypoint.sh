#!/bin/bash

# Load environment variables dari .env
export $(grep -v '^#' .env | xargs)

echo "linking storage..."
php artisan storage:link
echo "linking done."

echo "Menghapus cache lama..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "caching config..."
php artisan config:cache
echo "config cached."
echo "caching routes..."
php artisan route:cache
echo "routes cached."
echo "caching views..."
php artisan view:cache
echo "views cached."

# Generate APP_KEY
php artisan key:generate

# Fungsi untuk menunggu database siap
echo "Menunggu database siap..."
until php -r "try { new PDO('pgsql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}'); echo 'Database siap.'; } catch (PDOException \$e) { exit(1); }"; do
    sleep 3
    echo "Menunggu database..."
done || { echo "Gagal terhubung ke database."; exit 1; }

# Cek apakah migrasi diaktifkan di .env
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    echo "Menjalankan migrasi..."

    if php artisan migrate:fresh --force; then
        echo "Migrasi berhasil."
    else
        echo "Gagal menjalankan migrasi. Melakukan wipe..."
    fi
else
    echo "Migrasi dilewati karena pengaturan MIGRATE_ON_START tidak diatur ke true."
fi

# Jalankan seeder jika diatur di .env
if [ "${RUN_SEEDER}" = "true" ]; then
    echo "Menjalankan seeder..."
    if php artisan db:seed --force; then
        echo "Seeder berhasil."
    else
        echo "Gagal menjalankan seeder."
        exit 1
    fi
else
    echo "Seeder tidak dijalankan (RUN_SEEDER=false)."
fi

# Jalankan server Laravel
echo "Menjalankan server Laravel..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
# exec php-fpm