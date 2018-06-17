FROM php:7-apache
MAINTAINER Johannes Lauinger <johannes@lauinger-it.de>

RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip

RUN curl --silent --show-error https://getcomposer.org/installer | \
    php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

COPY src/ /var/www/html/
WORKDIR /var/www/html

RUN composer install
