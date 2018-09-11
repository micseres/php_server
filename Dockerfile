FROM zaherg/php72-swoole
LABEL maintainer "Andrii Yakovev <yawa20@gmail.com>"

USER root

WORKDIR /app
COPY composer.json ./
RUN composer install --no-dev
COPY . ./

CMD ["php", "start.php"]
