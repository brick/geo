ARG PHP_VERSION
FROM php:${PHP_VERSION}-cli

RUN apt-get update && apt-get install --yes libpq-dev
RUN docker-php-ext-install pdo pdo_pgsql

COPY --from=composer /usr/bin/composer /usr/bin/composer

CMD sleep infinity
