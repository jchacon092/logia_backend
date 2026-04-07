#!/bin/bash

echo "Esperando conexión a MySQL..."

# Esperar hasta que el puerto MySQL responda via TCP
until php -r "
\$conn = @fsockopen(getenv('DB_HOST'), getenv('DB_PORT'), \$errno, \$errstr, 3);
if (\$conn) { fclose(\$conn); exit(0); } else { exit(1); }
"; do
  echo "MySQL no disponible, reintentando en 3 segundos..."
  sleep 3
done

echo "Puerto MySQL abierto, esperando que esté listo..."
sleep 3

echo "Corriendo migraciones..."
php artisan migrate --force

echo "Enlazando storage..."
php artisan storage:link

echo "Limpiando caché..."
php artisan config:clear
php artisan cache:clear

echo "Iniciando servidor en puerto $PORT..."
php artisan config:cache
exec php -S 0.0.0.0:$PORT -t public public/index.php