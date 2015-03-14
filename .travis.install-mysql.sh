sudo apt-get remove mysql-server-5.5 mysql-server-core-5.5
sudo apt-get autoremove
sudo apt-get install libaio1
wget http://cdn.mysql.com/Downloads/MySQL-5.6/mysql-5.6.23-debian6.0-x86_64.deb
sudo dpkg -i mysql-5.6.23-debian6.0-x86_64.deb
sudo cp /opt/mysql/server-5.6/support-files/mysql.server /etc/init.d/mysql.server
sudo ln -sf /opt/mysql/server-5.6/bin/* /usr/bin/
sudo sed -i'' 's/table_cache/table_open_cache/' /etc/mysql/my.cnf
sudo sed -i'' 's/log_slow_queries/slow_query_log/' /etc/mysql/my.cnf
sudo sed -i'' 's/basedir[^=]\+=.*$/basedir = \/opt\/mysql\/server-5.6/' /etc/mysql/my.cnf
sudo /etc/init.d/mysql.server start
mysql -u root -e "SET @@global.sql_mode = NO_ENGINE_SUBSTITUTION"
mysql --version
