ARG PHP_VERSION
FROM php:${PHP_VERSION}-cli

RUN apt update && apt install --yes git libpq-dev libgeos-dev
RUN docker-php-ext-install pdo pdo_pgsql

RUN <<EOF
git clone https://git.osgeo.org/gitea/geos/php-geos.git
cd php-geos
./autogen.sh
./configure
make
mv modules/geos.so $(php -r "echo ini_get('extension_dir');")
echo "extension=geos.so" > /usr/local/etc/php/conf.d/geos.ini
EOF

COPY --from=composer /usr/bin/composer /usr/bin/composer

CMD sleep infinity
