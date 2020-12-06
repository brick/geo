set -e

sudo apt-get update
sudo apt-get autoremove postgis*
sudo apt-get autoremove postgresql*
sudo apt-get install postgresql-9.6-postgis-2.5
