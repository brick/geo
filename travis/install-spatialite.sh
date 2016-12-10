sudo apt-get update
sudo apt-get install libproj-dev libfreexl-dev libxml2-dev

wget http://download.osgeo.org/geos/geos-3.6.0.tar.bz2
tar jxf geos-3.6.0.tar.bz2
cd geos-3.6.0
./configure
make
sudo make install

wget http://www.gaia-gis.it/gaia-sins/libspatialite-sources/libspatialite-4.2.0.tar.gz
tar zxf libspatialite-4.2.0.tar.gz
cd libspatialite-4.2.0
./configure
make
sudo make install

if [[ $TRAVIS_PHP_VERSION != hhvm ]]; then
    printf "[sqlite3]\nsqlite3.extension_dir = /usr/local/lib" > sqlite.ini
    phpenv config-add sqlite.ini
fi
