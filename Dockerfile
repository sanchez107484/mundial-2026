FROM php:8.2-apache

RUN a2enmod rewrite

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY . /var/www/html/

RUN mkdir -p /var/www/html/data-init \
    && cp /var/www/html/data/*.json /var/www/html/data-init/ 2>/dev/null || true

RUN mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html/data

RUN echo "session.save_path = /var/www/html/data/sessions" >> "$PHP_INI_DIR/conf.d/sessions.ini" \
    && mkdir -p /var/www/html/data/sessions \
    && chown www-data:www-data /var/www/html/data/sessions

RUN sed -i 's/\r$//' /var/www/html/entrypoint.sh \
    && chmod +x /var/www/html/entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/var/www/html/entrypoint.sh"]
