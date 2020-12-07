sudo apt install mariadb-server-10.0

echo "
UPDATE mysql.user SET plugin = '' where User = 'root';
FLUSH PRIVILEGES;
" | sudo mysql
