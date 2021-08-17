ARG PHP_VERSION
FROM php:${PHP_VERSION}-cli

RUN apt-get update && apt-get install --yes libpq-dev
RUN docker-php-ext-install pdo pdo_pgsql

CMD sleep infinity
