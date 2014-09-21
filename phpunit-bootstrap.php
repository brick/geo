<?php

use Brick\Geo\Engine\GeometryEngine;
use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Engine\PDOEngine;
use Brick\Geo\Engine\SQLite3Engine;
use Brick\Geo\Engine\GEOSEngine;

require 'vendor/autoload.php';

/**
 * @return GeometryEngine
 */
function createGeometryEngine()
{
    switch ($engine = getenv('ENGINE')) {
        case 'PDO_MYSQL':
            $pdo = new PDO('mysql:host=localhost', 'root', '');
            $engine = new PDOEngine($pdo);
            break;

        case 'PDO_PGSQL':
            $pdo = new PDO('pgsql:host=localhost', 'postgres', '');
            $pdo->exec('CREATE EXTENSION IF NOT EXISTS postgis;');
            $engine = new PDOEngine($pdo);
            break;

        case 'SQLite3':
            $sqlite3 = new SQLite3(':memory:');
            $prefix = '';
            if (getenv('TRAVIS_PHP_VERSION') === 'hhvm') {
                $prefix = '/usr/lib/';
            }
            $sqlite3->loadExtension($prefix . 'libspatialite.so.3');
            $engine = new SQLite3Engine($sqlite3);
            break;

        case 'GEOS':
            $engine = new GEOSEngine();
            break;

        default:
            if ($engine === false) {
                echo 'ENGINE environment variable not set!' . PHP_EOL;
            } else {
                echo 'Unknown engine: ' . $engine . PHP_EOL;
            }

            echo 'Example usage: ENGINE={engine} vendor/bin/phpunit' . PHP_EOL;
            echo 'Available engines: PDO_MYSQL, PDO_PGSQL, SQLite3, GEOS' . PHP_EOL;
            exit(1);
    }

    echo 'Using ', get_class($engine), PHP_EOL;

    return $engine;
}

GeometryEngineRegistry::set(createGeometryEngine());
