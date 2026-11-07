FROM php:8.2-apache
WORKDIR /var/www/html
COPY . /var/www/html
RUN chmod +x /var/www/html/start.sh && mkdir -p /var/data && chown -R www-data:www-data /var/data /var/www/html/data
ENV DATA_DIR=/var/data
CMD ["/var/www/html/start.sh"]
