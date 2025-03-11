<?php

use Brick\Geo\Engine\PDOEngine;
use Brick\Geo\Engine\SQLite3Engine;
use Brick\Geo\Engine\GEOSEngine;

(function() {
    require 'vendor/autoload.php';

    $engine = getenv('ENGINE');

    if ($engine === false) {
        echo 'WARNING: running tests without a geometry engine.' . PHP_EOL;
        echo 'All tests requiring a geometry engine will be skipped.' . PHP_EOL;
        echo 'To run tests with a geometry engine, use: ENGINE={engine} vendor/bin/phpunit' . PHP_EOL;
        echo 'Available engines: PDO_MYSQL, PDO_PGSQL, SQLite3, GEOS' . PHP_EOL;
    } else {
        switch ($engine) {
            case 'PDO_MYSQL':
                $emulatePrepares = getenv('EMULATE_PREPARES') === 'ON';

                echo 'Using PDOEngine for MySQL' . PHP_EOL;
                echo 'with emulated prepares ' . ($emulatePrepares ? 'ON' : 'OFF') . PHP_EOL;

                $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $emulatePrepares);

                $statement = $pdo->query('SELECT VERSION()');
                $version = $statement->fetchColumn();

                echo 'MySQL version: ' . $version . PHP_EOL;

                $engine = new PDOEngine($pdo);
                break;

            case 'PDO_PGSQL':
                echo 'Using PDOEngine for PostgreSQL' . PHP_EOL;

                $credentials = [
                    'host' => $_ENV['POSTGRES_HOST'] ?? 'localhost',
                    'port' => $_ENV['POSTGRES_PORT'] ?? '5432',
                    'username' => $_ENV['DATABASE_USERNAME'] ?? 'postgres',
                    'password' => $_ENV['DATABASE_PASSWORD'] ?? 'postgres',
                ];

                $pdo = new PDO(
                    vsprintf('pgsql:host=%s;port=%d', [
                        $credentials['host'],
                        $credentials['port'],
                    ]),
                    $credentials['username'],
                    $credentials['password']
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $pdo->exec('CREATE EXTENSION IF NOT EXISTS postgis;');

                $statement = $pdo->query('SELECT version()');
                $version = $statement->fetchColumn();

                echo 'PostgreSQL version: ' . $version . PHP_EOL;

                $statement = $pdo->query('SELECT PostGIS_Version()');
                $version = $statement->fetchColumn();

                echo 'PostGIS version: ' . $version . PHP_EOL;

                $engine = new PDOEngine($pdo);
                break;

            case 'SQLite3':
                echo 'Using SQLite3Engine' . PHP_EOL;

                $sqlite3 = new SQLite3(':memory:');
                $sqlite3->enableExceptions(true);

                $sqliteVersion = $sqlite3->querySingle('SELECT sqlite_version()');
                echo 'SQLite version: ' . $sqliteVersion . PHP_EOL;

                $sqlite3->loadExtension('mod_spatialite.so');

                $spatialiteVersion = $sqlite3->querySingle('SELECT spatialite_version()');
                echo 'SpatiaLite version: ' . $spatialiteVersion . PHP_EOL;

                $sqlite3->exec('SELECT InitSpatialMetaData()');

                $engine = new SQLite3Engine($sqlite3);
                break;

            case 'GEOS':
                echo 'Using GEOSEngine' . PHP_EOL;
                echo 'GEOS version: ' . GEOSVersion() . PHP_EOL;

                $engine = new GEOSEngine();
                break;

            default:
                echo 'Unknown engine: ' . $engine . PHP_EOL;
                exit(1);
        }

        $GLOBALS['GEOMETRY_ENGINE'] = $engine;
    }
})();
