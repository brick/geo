wget https://github.com/libgeos/libgeos/archive/$VERSION.tar.gz
tar zxf $VERSION.tar.gz
cd libgeos-$VERSION
./autogen.sh
./configure --enable-php
make
mv php/.libs/geos.so $(php-config --extension-dir)
cd ..

echo "extension=geos.so" > geos.ini
phpenv config-add geos.ini
