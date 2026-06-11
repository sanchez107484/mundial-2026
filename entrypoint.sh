#!/bin/bash
set -e

PORT=${PORT:-8080}

sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

if [ ! -f /var/www/html/data/teams.json ]; then
    cp /var/www/html/data-init/*.json /var/www/html/data/ 2>/dev/null || true
fi

chown -R www-data:www-data /var/www/html/data

exec apache2-foreground
