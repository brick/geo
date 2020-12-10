set -e

sudo apt update
sudo apt autoremove postgis*
sudo apt autoremove postgresql*
sudo apt install postgresql-11 postgresql-11-postgis-2.5
sudo service postgresql start 11

psql -d postgres -c "CREATE EXTENSION postgis;"
