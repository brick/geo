set -e

sudo apt update
sudo apt autoremove postgis*
sudo apt autoremove postgresql*
sudo apt install postgresql-13-postgis-3
sudo service postgresql start 13

psql -d postgres -c "CREATE EXTENSION postgis;"
