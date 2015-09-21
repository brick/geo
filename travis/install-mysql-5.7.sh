service mysql stop

apt-get purge '^mysql*' 'libmysql*'
apt-get autoremove
apt-get autoclean

sudo rm -rf /var/lib/mysql
sudo rm -rf /var/log/mysql

apt-get install python-software-properties
apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0x8C718D3B5072E1F5
add-apt-repository 'deb http://repo.mysql.com/apt/ubuntu/ precise mysql-5.7-dmr'

apt-get update

echo mysql-community-server mysql-community-server/root-pass password "" | debconf-set-selections
echo mysql-community-server mysql-community-server/re-root-pass password "" | debconf-set-selections

DEBIAN_FRONTEND=noninteractive apt-get -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confold -q -y install mysql-server libmysqlclient-dev

mysql --version
mysql --user=root -e "UPDATE mysql.user SET plugin='mysql_native_password'; FLUSH PRIVILEGES";
