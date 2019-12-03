set -e

sudo apt-get update
sudo apt-get autoremove postgis*
sudo apt-get autoremove postgresql*
sudo apt-get install postgresql-9.3-postgis-2.1
