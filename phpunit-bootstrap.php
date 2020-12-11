<?php

use Brick\Geo\Doctrine\Types;
use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Engine\PDOEngine;
use Brick\Geo\Engine\SQLite3Engine;
use Brick\Geo\Engine\GEOSEngine;
use Doctrine\DBAL\Types\Type;

(function() {
    /** @var \Composer\Autoload\ClassLoader $classLoader */
    $classLoader = require 'vendor/autoload.php';

    // Add namespace for doctrine base tests
    $classLoader->addPsr4('Doctrine\\Tests\\', [
        __DIR__ . '/vendor/doctrine/orm/tests/Doctrine/Tests',
        __DIR__ . '/vendor/doctrine/dbal/tests/Doctrine/Tests'
    ]);
    $classLoader->loadClass('Doctrine\Tests\DbalFunctionalTestCase');
    $classLoader->loadClass('Doctrine\Tests\DBAL\Mocks\MockPlatform');

    Type::addType('geometry', Types\GeometryType::class);
    Type::addType('linestring', Types\LineStringType::class);
    Type::addType('multilinestring', Types\MultiLineStringType::class);
    Type::addType('multipoint', Types\MultiPointType::class);
    Type::addType('multipolygon', Types\MultiPolygonType::class);
    Type::addType('point', Types\PointType::class);
    Type::addType('polygon', Types\PolygonType::class);

    $engine = getenv('ENGINE');

    if ($engine === false) {
        echo 'WARNING: running tests without a geometry engine.' . PHP_EOL;
        echo 'All tests requiring a geometry engine will be skipped.' . PHP_EOL;
        echo 'To run tests with a geometry engine, use: ENGINE={engine} vendor/bin/phpunit' . PHP_EOL;
        echo 'Available engines: PDO_MYSQL, PDO_PGSQL, SQLite3, GEOS' . PHP_EOL;
    } else {
        switch ($engine) {
            case 'PDO_MYSQL':
                echo 'Using PDOEngine for MySQL' . PHP_EOL;

                $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $pdo->exec('DROP DATABASE IF EXISTS geo_tests');
                $pdo->exec('DROP DATABASE IF EXISTS geo_tests_tmp');
                $pdo->exec('CREATE DATABASE geo_tests');
                $pdo->exec('CREATE DATABASE geo_tests_tmp');
                $pdo->exec('USE geo_tests');

                $statement = $pdo->query('SELECT VERSION()');
                $version = $statement->fetchColumn();

                echo 'MySQL version: ' . $version . PHP_EOL;

                // Connect data for doctrine integration tests
                $GLOBALS['db_type'] = 'pdo_mysql';
                $GLOBALS['db_host'] = '127.0.0.1';
                $GLOBALS['db_port'] = 3306;
                $GLOBALS['db_username'] = 'root';
                $GLOBALS['db_password'] = '';
                $GLOBALS['db_name'] = 'geo_tests';

                $GLOBALS['tmpdb_type'] = 'pdo_mysql';
                $GLOBALS['tmpdb_host'] = '127.0.0.1';
                $GLOBALS['tmpdb_port'] = 3306;
                $GLOBALS['tmpdb_username'] = 'root';
                $GLOBALS['tmpdb_password'] = '';
                $GLOBALS['tmpdb_name'] = 'geo_tests_tmp';

                $engine = new PDOEngine($pdo);
                break;

            case 'PDO_PGSQL':
                echo 'Using PDOEngine for PostgreSQL' . PHP_EOL;

                $pdo = new PDO('pgsql:host=localhost', 'postgres', 'postgres');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $pdo->exec('CREATE EXTENSION IF NOT EXISTS postgis;');
                $pdo->exec('DROP DATABASE IF EXISTS geo_tests');
                $pdo->exec('DROP DATABASE IF EXISTS geo_tests_tmp');
                $pdo->exec('CREATE DATABASE geo_tests');
                $pdo->exec('CREATE DATABASE geo_tests_tmp');

                $statement = $pdo->query('SELECT version()');
                $version = $statement->fetchColumn(0);

                echo 'PostgreSQL version: ' . $version . PHP_EOL;

                $statement = $pdo->query('SELECT PostGIS_Version()');
                $version = $statement->fetchColumn(0);

                echo 'PostGIS version: ' . $version . PHP_EOL;

                // Connect data for doctrine integration tests
                $GLOBALS['db_type'] = 'pdo_pgsql';
                $GLOBALS['db_host'] = 'localhost';
                $GLOBALS['db_port'] = 5432;
                $GLOBALS['db_username'] = 'postgres';
                $GLOBALS['db_password'] = 'postgres';
                $GLOBALS['db_name'] = 'geo_tests';

                $GLOBALS['tmpdb_type'] = 'pdo_pgsql';
                $GLOBALS['tmpdb_host'] = 'localhost';
                $GLOBALS['tmpdb_port'] = 5432;
                $GLOBALS['tmpdb_username'] = 'postgres';
                $GLOBALS['tmpdb_password'] = 'postgres';
                $GLOBALS['tmpdb_name'] = 'geo_tests_tmp';

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

        GeometryEngineRegistry::set($engine);
    }
})();
