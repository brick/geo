echo mysql-apt-config mysql-apt-config/select-server select mysql-5.7 | sudo debconf-set-selections
wget https://dev.mysql.com/get/mysql-apt-config_0.8.14-1_all.deb
sudo dpkg --install mysql-apt-config_0.8.14-1_all.deb
sudo apt-get update -q
sudo apt-get install -q -y --force-yes -o Dpkg::Options::=--force-confnew mysql-server
sudo /etc/init.d/mysql start
sudo mysql_upgrade
