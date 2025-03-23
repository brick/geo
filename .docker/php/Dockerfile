ARG PHP_VERSION

# Build a statically linked version of geosop
# ===========================================
FROM ubuntu:24.04 AS geosop-builder

ARG GEOSOP_VERSION
SHELL ["/bin/sh", "-e", "-c"]

RUN <<EOF
apt update
apt install --yes git cmake g++
EOF

RUN <<EOF
git clone --depth 1 --branch ${GEOSOP_VERSION} https://github.com/libgeos/geos.git
mkdir geos/build
cd geos/build
cmake .. -DBUILD_SHARED_LIBS=OFF -DCMAKE_EXE_LINKER_FLAGS="-static -static-libstdc++ -static-libgcc"
cmake --build . -- -j$(nproc)
make install
EOF

# Build the GEOS php extension
# ============================

FROM php:${PHP_VERSION}-cli AS geos-php-builder

SHELL ["/bin/sh", "-e", "-c"]

RUN <<EOF
apt update
apt install --yes git libgeos-dev
EOF

RUN <<EOF
git clone https://git.osgeo.org/gitea/geos/php-geos.git
cd php-geos
./autogen.sh
./configure
make
EOF

# Final image
# ===========

FROM php:${PHP_VERSION}-cli

ARG UID
ARG GID

SHELL ["/bin/sh", "-e", "-c"]

RUN <<EOF
apt update
apt install --yes git libzip-dev libpq-dev libsqlite3-mod-spatialite
rm -rf /var/lib/apt/lists/*
EOF

RUN docker-php-ext-install zip pdo pdo_pgsql pdo_mysql

# SQLite3 configuration
RUN echo "[sqlite3]\nsqlite3.extension_dir = /usr/lib/x86_64-linux-gnu"> /usr/local/etc/php/conf.d/sqlite3.ini

# GEOS PHP extension
COPY --from=geos-php-builder /php-geos/modules/geos.so /usr/local/lib/php/extensions/no-debug-non-zts-20240924/geos.so
RUN echo "extension=geos.so" > /usr/local/etc/php/conf.d/geos.ini
RUN php -r "exit(extension_loaded('geos') ? 0 : 1);"

# geosop CLI
COPY --from=geosop-builder /usr/local/bin/geosop /usr/local/bin/geosop

# Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN groupadd -g ${GID} user
RUN useradd -u ${UID} -g ${GID} -m -s /bin/bash user

USER user

CMD sleep infinity
