#!/bin/bash
set -e

PORT=${PORT:-8080}

sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/sites-available/default

php-fpm -D

nginx -g "daemon off;"
