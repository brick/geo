sudo apt-get update
sudo apt-get remove 'libgeos.*'
sudo apt-get autoremove

wget https://github.com/libgeos/libgeos/archive/$VERSION.tar.gz
tar zxf $VERSION.tar.gz
cd libgeos-$VERSION
./autogen.sh
./configure --prefix=/usr
make
sudo make install
cd ..

wget https://git.osgeo.org/gogs/geos/php-geos/archive/1.0.0rc1.tar.gz
tar zxf 1.0.0rc1.tar.gz
cd php-geos
./autogen.sh
./configure
make
mv modules/geos.so $(php-config --extension-dir)
cd ..

echo "extension=geos.so" > geos.ini
phpenv config-add geos.ini
