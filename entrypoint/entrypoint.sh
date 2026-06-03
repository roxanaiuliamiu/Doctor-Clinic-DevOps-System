#!/bin/sh
set -e

echo "Waiting for database..."
until php -r "
try {
    new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    echo 'Database is ready.' . PHP_EOL;
} catch (Exception \$e) {
    exit(1);
}
"; do
  sleep 2
done

echo "Running migrations..."
php artisan migrate --force

echo "Clearing and caching..."
php artisan optimize:clear || true

exec php-fpm