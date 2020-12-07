apt install libsqlite3-mod-spatialite
echo "sqlite3.extension_dir = /usr/lib/x86_64-linux-gnu" >> "/home/travis/.phpenv/versions/$PHP_VERSION/etc/php.ini"
