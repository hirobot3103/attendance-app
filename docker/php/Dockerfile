FROM php:8.3.0-fpm

COPY php.ini /usr/local/etc/php/
# COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN apt update \
  && apt install -y default-mysql-client zlib1g-dev libzip-dev unzip \
  && docker-php-ext-install pdo_mysql zip \
  && pecl install xdebug \
  && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && composer self-update

RUN useradd -m hirobot3103

WORKDIR /var/www
