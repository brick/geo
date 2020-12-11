sudo apt update
sudo apt install mariadb-server-10.1 mariadb-client-10.1

echo "
UPDATE mysql.user SET plugin = '' where User = 'root';
FLUSH PRIVILEGES;
" | sudo mysql
