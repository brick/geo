<?php

use Brick\Geo\Service\GeometryService;
use Brick\Geo\Service\GeometryServiceRegistry;
use Brick\Geo\Service\PDOService;
use Brick\Geo\Service\SQLite3Service;

require 'vendor/autoload.php';

/**
 * @return GeometryService
 */
function getGeometryService()
{
    switch ($db = getenv('DB')) {
        case 'mysql':
            $pdo = new PDO('mysql:host=localhost', 'root', '');
            return new PDOService($pdo);

        case 'pgsql':
            $pdo = new PDO('pgsql:host=localhost', 'postgres', '');
            $pdo->exec('CREATE EXTENSION IF NOT EXISTS postgis;');
            return new PDOService($pdo);

        case 'sqlite':
            $sqlite3 = new SQLite3(':memory:');
            $prefix = '';
            if (substr(getenv('TRAVIS_PHP_VERSION'), 0, 4) === 'hhvm') {
                $prefix = '/usr/lib/';
            }
            $sqlite3->loadExtension($prefix . 'libspatialite.so.3');
            return new SQLite3Service($sqlite3);

        default:
            if ($db === false) {
                echo 'DB environment variable not set!' . PHP_EOL;
            } else {
                echo 'Unknown DB: ' . $db . PHP_EOL;
            }

            echo 'Use: DB=mysql|pgsql|sqlite vendor/bin/phpunit' . PHP_EOL;
            exit(1);
    }
}

GeometryServiceRegistry::set(getGeometryService());
