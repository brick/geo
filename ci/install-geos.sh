set -e

GEOS_VERSION=3.8.0
PHP_GEOS_VERSION=1.0.0

sudo apt update
sudo apt autoremove

wget https://github.com/libgeos/geos/archive/$GEOS_VERSION.tar.gz
tar zxf $GEOS_VERSION.tar.gz
cd geos-$GEOS_VERSION
./autogen.sh
./configure --prefix=/usr
make
sudo make install
cd ..

wget https://github.com/libgeos/php-geos/archive/$PHP_GEOS_VERSION.tar.gz
tar zxf $PHP_GEOS_VERSION.tar.gz
cd php-geos-$PHP_GEOS_VERSION
./autogen.sh
./configure
make
sudo mv modules/geos.so $(php-config --extension-dir)
cd ..

echo "extension=geos.so" > geos.ini
