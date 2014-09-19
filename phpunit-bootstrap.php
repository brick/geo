<?php

use Brick\Geo\Service\GeometryService;
use Brick\Geo\Service\GeometryServiceRegistry;
use Brick\Geo\Service\PDOService;
use Brick\Geo\Service\SQLite3Service;
use Brick\Geo\Service\GEOSService;

require 'vendor/autoload.php';

/**
 * @return GeometryService
 */
function createGeometryService()
{
    switch ($service = getenv('SERVICE')) {
        case 'PDO_MYSQL':
            $pdo = new PDO('mysql:host=localhost', 'root', '');
            $service = new PDOService($pdo);
            break;

        case 'PDO_PGSQL':
            $pdo = new PDO('pgsql:host=localhost', 'postgres', '');
            $pdo->exec('CREATE EXTENSION IF NOT EXISTS postgis;');
            $service = new PDOService($pdo);
            break;

        case 'SQLite3':
            $sqlite3 = new SQLite3(':memory:');
            $prefix = '';
            if (getenv('TRAVIS_PHP_VERSION') === 'hhvm') {
                $prefix = '/usr/lib/';
            }
            $sqlite3->loadExtension($prefix . 'libspatialite.so.3');
            $service = new SQLite3Service($sqlite3);
            break;

        case 'GEOS':
            $service = new GEOSService();
            break;

        default:
            if ($service === false) {
                echo 'SERVICE environment variable not set!' . PHP_EOL;
            } else {
                echo 'Unknown service: ' . $service . PHP_EOL;
            }

            echo 'Example usage: SERVICE={service} vendor/bin/phpunit' . PHP_EOL;
            echo 'Available services: PDO_MYSQL, PDO_PGSQL, SQLite3, GEOS' . PHP_EOL;
            exit(1);
    }

    echo 'Using ', get_class($service), PHP_EOL;

    return $service;
}

GeometryServiceRegistry::set(createGeometryService());
