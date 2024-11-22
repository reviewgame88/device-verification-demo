#!/bin/bash

# Đợi MySQL sẵn sàng và có thể kết nối
until php -r "try {new PDO('mysql:host=mysql;dbname=demo', 'root', 'root');} catch(PDOException \$e) {exit(1);} exit(0);" ; do
  echo "Waiting for MySQL connection..."
  sleep 1
done
echo "MySQL is ready and connected!"

# Install dependencies
composer install

# Generate key if not exists
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Run migrations
php artisan migrate:fresh --seed

# Create storage link
php artisan storage:link

# Set permissions
chmod -R 777 storage bootstrap/cache

# Start PHP-FPM
php-fpm