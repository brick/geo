set -e

sudo apt update
sudo apt install postgresql-11-postgis-2.5
sudo service postgresql start 11

psql -d postgres -c "CREATE EXTENSION postgis;"
