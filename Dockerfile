FROM php:7.2-alpine
LABEL maintainer="Andrii Yakovev <yawa20@gmail.com>"

ENV COMPOSER_ALLOW_SUPERUSER 1

USER root

RUN set -ex \
  	&& apk update \
    && apk add --no-cache git mysql-client curl openssh-client icu libpng freetype libjpeg-turbo postgresql-dev libffi-dev libsodium \
    && apk add --no-cache --virtual build-dependencies icu-dev libxml2-dev freetype-dev libpng-dev libjpeg-turbo-dev g++ make autoconf libsodium-dev \
    && docker-php-source extract \
    && pecl install swoole redis sodium \
    && docker-php-ext-enable redis swoole sodium \
    && docker-php-source delete \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) pgsql pdo_mysql pdo_pgsql intl zip gd \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && cd  / && rm -fr /src \
    && apk del build-dependencies \
    && rm -rf /tmp/*

RUN apk add --no-cache libmcrypt libmcrypt-dev gcc g++ autoconf make
RUN pecl install mcrypt-1.0.1 && echo extension=mcrypt.so > /usr/local/etc/php/conf.d/mcrypt.ini

RUN  docker-php-ext-configure sockets --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install sockets

WORKDIR /app
COPY composer.json /app
RUN composer install --no-dev
COPY . /app

CMD ["php", "start.php"]
