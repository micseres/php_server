FROM zaherg/php72-swoole
LABEL maintainer "Andrii Yakovev <yawa20@gmail.com>"

USER root

RUN apk add --no-cache libmcrypt libmcrypt-dev gcc g++ autoconf make
RUN pecl install mcrypt-1.0.1
RUN echo extension=mcrypt.so > /usr/local/etc/php/conf.d/mcrypt.ini

WORKDIR /app
COPY composer.json ./
RUN composer install --no-dev
COPY . ./

CMD ["php", "start.php"]
