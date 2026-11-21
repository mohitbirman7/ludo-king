#!/bin/sh
P=${PORT:-10000}
sed -i "s/Listen 80/Listen ${P}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${P}>/" /etc/apache2/sites-available/000-default.conf
mkdir -p "$DATA_DIR" && chown -R www-data:www-data "$DATA_DIR"
exec apache2-foreground
