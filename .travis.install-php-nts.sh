PHP_VERSION=${TRAVIS_PHP_VERSION}snapshot

# install php-build plugin
git clone git://github.com/CHH/php-build.git $HOME/.phpenv/plugins/php-build

# install PHP
phpenv install $PHP_VERSION
phpenv global $PHP_VERSION
php --version
