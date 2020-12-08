sudo apt update
sudo apt install mariadb-server

echo "
UPDATE mysql.user SET plugin = '' where User = 'root';
FLUSH PRIVILEGES;
" | sudo mysql
