sudo apt-get remove --purge "^mysql.*"
sudo apt-get autoremove
sudo apt-get autoclean
sudo apt-add-repository ppa:ondrej/mysql-5.6 -y
sudo apt-get update
sudo apt-get install mysql-server-5.6 mysql-client-5.6
mysql -uroot -e "SET @@global.sql_mode = NO_ENGINE_SUBSTITUTION"
mysql --version
