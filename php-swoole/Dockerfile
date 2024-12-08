FROM openswoole/swoole

RUN apt-get update && \
    apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    libssl-dev \
    libmariadb-dev \
    netcat \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

COPY custom-php.ini /usr/local/etc/php/conf.d/

WORKDIR /var/www/html

COPY . .

ENTRYPOINT ["php", "/var/www/html/server.php"]