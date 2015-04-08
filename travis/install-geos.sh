GEOS_VERSION=svn-trunk

wget https://github.com/libgeos/libgeos/archive/$GEOS_VERSION.tar.gz
tar zxf $GEOS_VERSION.tar.gz
cd libgeos-$GEOS_VERSION
./autogen.sh
./configure --enable-php
make
mv php/.libs/geos.so $(php-config --extension-dir)
cd ..

echo "extension=geos.so" > geos.ini
phpenv config-add geos.ini
