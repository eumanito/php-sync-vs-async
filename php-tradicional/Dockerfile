FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    apache2 \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

RUN docker-php-ext-install mysqli

COPY apache.conf /etc/apache2/sites-available/000-default.conf

COPY html /var/www/html

#COPY create_table.sql /docker-entrypoint-initdb.d/

COPY custom-php.ini /usr/local/etc/php/conf.d/

EXPOSE 80

CMD ["apache2ctl", "-D", "FOREGROUND"]