sudo apt-get update
sudo apt-get remove 'libgeos.*'
sudo apt-get autoremove

wget https://github.com/libgeos/geos/archive/$VERSION.tar.gz
tar zxf $VERSION.tar.gz
cd geos-$VERSION
./autogen.sh
./configure --prefix=/usr
make
sudo make install
cd ..

wget https://github.com/libgeos/php-geos/archive/1.0.0.tar.gz
tar zxf 1.0.0.tar.gz
cd php-geos-1.0.0
./autogen.sh
./configure
make
mv modules/geos.so $(php-config --extension-dir)
cd ..

echo "extension=geos.so" > geos.ini
phpenv config-add geos.ini
