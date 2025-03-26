<?php

use Brick\Geo\Engine\Database\Driver\PdoMysqlDriver;
use Brick\Geo\Engine\Database\Driver\PdoPgsqlDriver;
use Brick\Geo\Engine\Database\Driver\PgsqlDriver;
use Brick\Geo\Engine\Database\Driver\Sqlite3Driver;
use Brick\Geo\Engine\GeosEngine;
use Brick\Geo\Engine\GeosOpEngine;
use Brick\Geo\Engine\MariadbEngine;
use Brick\Geo\Engine\MysqlEngine;
use Brick\Geo\Engine\PostgisEngine;
use Brick\Geo\Engine\SpatialiteEngine;

function getOptionalEnv(string $name): ?string
{
    $value = getenv($name);

    return $value === false ? null : $value;
}

function getOptionalEnvOrDefault(string $name, string $default): string
{
    $value = getenv($name);

    return $value === false ? $default : $value;
}

function getRequiredEnv(string $name): string
{
    $value = getenv($name);

    if ($value === false) {
        echo 'Missing environment variable: ', $name, PHP_EOL;
        exit(1);
    }

    return $value;
}

(function() {
    require 'vendor/autoload.php';

    $engine = getOptionalEnv('ENGINE');

    if ($engine === null) {
        echo 'WARNING: running tests without a geometry engine.', PHP_EOL;
        echo 'All tests requiring a geometry engine will be skipped.', PHP_EOL;
        echo 'To run tests with a geometry engine, use: ENGINE={engine} vendor/bin/phpunit', PHP_EOL;
        echo 'Available engines: geos, geosop, mysql_pdo, mariadb_pdo, postgis_pdo, postgis_pgsql, spatialite_sqlite3', PHP_EOL;
    } else {
        $driver = null;

        switch ($engine) {
            case 'geos':
                echo 'Using GeosEngine', PHP_EOL;
                echo 'GEOS version: ', GEOSVersion(), PHP_EOL;

                $engine = new GeosEngine();
                break;

            case 'geosop':
                echo 'Using GeosOpEngine', PHP_EOL;

                $geosopPath = getRequiredEnv('GEOSOP_PATH');
                $engine = new GeosOpEngine($geosopPath);

                echo 'geosop version: ', $engine->getGeosOpVersion(), PHP_EOL;
                break;

            case 'mysql_pdo':
                $emulatePrepares = getOptionalEnv('EMULATE_PREPARES') === 'ON';

                echo 'Using MysqlEngine with PdoMysqlDriver', PHP_EOL;
                echo 'with emulated prepares ', ($emulatePrepares ? 'ON' : 'OFF'), PHP_EOL;

                $host = getRequiredEnv('MYSQL_HOST');
                $port = getOptionalEnvOrDefault('MYSQL_PORT', '3306');
                $username = getRequiredEnv('MYSQL_USER');
                $password = getRequiredEnv('MYSQL_PASSWORD');

                $dsn = sprintf('mysql:host=%s;port=%d', $host, $port);
                $pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => $emulatePrepares,
                ]);

                $driver = new PdoMysqlDriver($pdo);
                $engine = new MysqlEngine($driver);

                $version = $driver->executeQuery('SELECT VERSION()')->get(0)->asString();
                echo 'MySQL version: ', $version, PHP_EOL;

                break;

            case 'mariadb_pdo':
                $emulatePrepares = getOptionalEnv('EMULATE_PREPARES') === 'ON';

                echo 'Using MariadbEngine with PdoMysqlDriver', PHP_EOL;
                echo 'with emulated prepares ', ($emulatePrepares ? 'ON' : 'OFF'), PHP_EOL;

                $host = getRequiredEnv('MARIADB_HOST');
                $port = getOptionalEnvOrDefault('MARIADB_PORT', '3306');
                $username = getRequiredEnv('MARIADB_USER');
                $password = getRequiredEnv('MARIADB_PASSWORD');

                $dsn = sprintf('mysql:host=%s;port=%d', $host, $port);
                $pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => $emulatePrepares,
                ]);

                $driver = new PdoMysqlDriver($pdo);
                $engine = new MariadbEngine($driver);

                $version = $driver->executeQuery('SELECT VERSION()')->get(0)->asString();
                echo 'MariaDB version: ', $version, PHP_EOL;

                break;

            case 'postgis_pdo':
                $emulatePrepares = getOptionalEnv('EMULATE_PREPARES') === 'ON';

                echo 'Using PostgisEngine with PdoPgsqlDriver', PHP_EOL;
                echo 'with emulated prepares ', ($emulatePrepares ? 'ON' : 'OFF'), PHP_EOL;

                $host = getRequiredEnv('POSTGRES_HOST');
                $port = getOptionalEnvOrDefault('POSTGRES_PORT', '5432');
                $username = getRequiredEnv('POSTGRES_USER');
                $password = getRequiredEnv('POSTGRES_PASSWORD');

                $pdo = new PDO(
                    sprintf('pgsql:host=%s;port=%d', $host, $port),
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_EMULATE_PREPARES => $emulatePrepares,
                    ],
                );

                $driver = new PdoPgsqlDriver($pdo);
                $engine = new PostgisEngine($driver);

                $version = $driver->executeQuery('SELECT version()')->get(0)->asString();
                echo 'PostgreSQL version: ', $version, PHP_EOL;

                $version = $driver->executeQuery('SELECT PostGIS_Version()')->get(0)->asString();
                echo 'PostGIS version: ', $version, PHP_EOL;

                $version = $driver->executeQuery('SELECT PostGIS_GEOS_Version()')->get(0)->asString();
                echo 'PostGIS GEOS version: ', $version, PHP_EOL;

                break;

            case 'postgis_pgsql':
                echo 'Using PostgisEngine with PgsqlDriver', PHP_EOL;

                $host = getRequiredEnv('POSTGRES_HOST');
                $port = getOptionalEnvOrDefault('POSTGRES_PORT', '5432');
                $username = getRequiredEnv('POSTGRES_USER');
                $password = getRequiredEnv('POSTGRES_PASSWORD');

                $connection = pg_connect(sprintf(
                    'host=%s port=%d user=%s password=%s',
                    $host,
                    $port,
                    $username,
                    $password,
                ));

                $driver = new PgsqlDriver($connection);
                $engine = new PostgisEngine($driver);

                $version = $driver->executeQuery('SELECT version()')->get(0)->asString();
                echo 'PostgreSQL version: ', $version, PHP_EOL;

                $version = $driver->executeQuery('SELECT PostGIS_Version()')->get(0)->asString();
                echo 'PostGIS version: ', $version, PHP_EOL;

                $version = $driver->executeQuery('SELECT PostGIS_GEOS_Version()')->get(0)->asString();
                echo 'PostGIS GEOS version: ', $version, PHP_EOL;

                break;

            case 'spatialite_sqlite3':
                echo 'Using SpatialiteEngine with Sqlite3Driver', PHP_EOL;

                $sqlite3 = new SQLite3(':memory:');
                $sqlite3->enableExceptions(true);

                $driver = new Sqlite3Driver($sqlite3);

                $sqliteVersion = $driver->executeQuery('SELECT sqlite_version()')->get(0)->asString();
                echo 'SQLite version: ', $sqliteVersion, PHP_EOL;

                $sqlite3->loadExtension('mod_spatialite.so');

                $spatialiteVersion = $driver->executeQuery('SELECT spatialite_version()')->get(0)->asString();
                echo 'SpatiaLite version: ', $spatialiteVersion, PHP_EOL;

                $sqlite3->exec('SELECT InitSpatialMetaData()');

                $engine = new SpatialiteEngine($driver);
                break;

            default:
                echo 'Unknown engine: ', $engine, PHP_EOL;
                exit(1);
        }

        $GLOBALS['GEOMETRY_ENGINE'] = $engine;

        if ($driver !== null) {
            $GLOBALS['DATABASE_DRIVER'] = $driver;
        }
    }
})();
