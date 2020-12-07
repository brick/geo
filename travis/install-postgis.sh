set -e

sudo apt-get update
sudo apt-get autoremove postgis*
sudo apt-get autoremove postgresql*

if [[ "$TRAVIS_DIST" = "xenial" ]]
then sudo apt-get install postgresql-9.6-postgis-2.5
else sudo apt-get install postgresql-9.3-postgis-2.1
fi
