ARG PHP_VERSION
FROM php:${PHP_VERSION}-cli

ARG GEOSOP_VERSION
SHELL ["/bin/sh", "-e", "-c"]

RUN apt update && apt install --yes git cmake libpq-dev libgeos-dev libsqlite3-mod-spatialite
RUN docker-php-ext-install pdo pdo_pgsql pdo_mysql

# SQLite3 configuration
RUN echo "[sqlite3]\nsqlite3.extension_dir = /usr/lib/x86_64-linux-gnu"> /usr/local/etc/php/conf.d/sqlite3.ini

# GEOS PHP extension
RUN <<EOF
git clone https://git.osgeo.org/gitea/geos/php-geos.git
cd php-geos
./autogen.sh
./configure
make
mv modules/geos.so $(php -r "echo ini_get('extension_dir');")
echo "extension=geos.so" > /usr/local/etc/php/conf.d/geos.ini
cd ..
rm -rf php-geos
EOF

# geosop CLI
RUN <<EOF
git clone --depth 1 --branch ${GEOSOP_VERSION} https://github.com/libgeos/geos.git
mkdir geos/build
cd geos/build
cmake .. -DBUILD_SHARED_LIBS=OFF -DCMAKE_EXE_LINKER_FLAGS="-static -static-libstdc++ -static-libgcc"
cmake --build . -- -j$(nproc)
make install
cd ../..
rm -rf geos
EOF

# Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

CMD sleep infinity
