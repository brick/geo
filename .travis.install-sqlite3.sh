if [[ $TRAVIS_PHP_VERSION != hhvm ]]; then
    printf "[sqlite3]\nsqlite3.extension_dir = /usr/lib" > sqlite.ini
    phpenv config-add sqlite.ini
fi
