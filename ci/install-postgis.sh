set -e

sudo apt update
sudo apt autoremove postgis*
sudo apt autoremove postgresql*
sudo apt install postgresql-11 postgresql-11-postgis-2.5

echo "starting"
sudo service postgresql start 11

echo "sleeping"
sleep 10

echo "create ext"
psql -d postgres -c "CREATE EXTENSION postgis;"
