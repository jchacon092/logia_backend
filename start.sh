#!/bin/bash

echo "Esperando conexión a MySQL..."

# Esperar hasta que MySQL responda
until php artisan db:show > /dev/null 2>&1; do
  echo "MySQL no disponible, reintentando en 3 segundos..."
  sleep 3
done

echo "MySQL listo! Corriendo migraciones..."
php artisan migrate --force

echo "Enlazando storage..."
php artisan storage:link

echo "Iniciando servidor..."
php artisan serve --host=0.0.0.0 --port=$PORT
