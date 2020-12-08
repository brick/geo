set -e

sudo apt update
sudo apt autoremove postgis*
sudo apt autoremove postgresql*
sudo apt install postgresql-10-postgis-2.4
sudo service postgresql start 10

psql -d postgres -c "CREATE EXTENSION postgis;"
