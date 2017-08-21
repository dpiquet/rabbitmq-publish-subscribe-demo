FROM php:5.6-fpm
MAINTAINER Damien PIQUET <dpiquet@teicee.com>

ARG PHP_APCU_VERSION=4.0.11
ARG PHP_XDEBUG_VERSION=2.4.1

RUN apt-get update \
    && apt-get install -y \
        libicu-dev \
        zlib1g-dev \
        postgresql-server-dev-all \
    && docker-php-source extract \
    && curl -L -o /tmp/apcu-$PHP_APCU_VERSION.tgz https://pecl.php.net/get/apcu-$PHP_APCU_VERSION.tgz \
    && curl -L -o /tmp/xdebug-$PHP_XDEBUG_VERSION.tgz http://xdebug.org/files/xdebug-$PHP_XDEBUG_VERSION.tgz \
    && tar xfz /tmp/apcu-$PHP_APCU_VERSION.tgz \
    && tar xfz /tmp/xdebug-$PHP_XDEBUG_VERSION.tgz \
    && rm -r \
        /tmp/apcu-$PHP_APCU_VERSION.tgz \
        /tmp/xdebug-$PHP_XDEBUG_VERSION.tgz \
    && mv apcu-$PHP_APCU_VERSION /usr/src/php/ext/apcu \
    && mv xdebug-$PHP_XDEBUG_VERSION /usr/src/php/ext/xdebug \
    && docker-php-ext-install \
        apcu \
        intl \
        mbstring \
        mysqli \
        xdebug \
        zip \
        pdo \
        pdo_pgsql \
        bcmath \
    && docker-php-source delete \
    && php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer \
    && chmod +x /usr/local/bin/composer \
    && curl -L -o /usr/bin/phpunit https://phar.phpunit.de/phpunit-5.7.phar \
    && chmod +x /usr/bin/phpunit \
    && /usr/bin/phpunit --version
