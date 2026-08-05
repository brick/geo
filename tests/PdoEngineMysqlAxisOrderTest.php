<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\Engine\MysqlGeoAxisOrder;
use Brick\Geo\Engine\PdoEngine;
use Brick\Geo\Point;
use PDO;

use function strpos;

/**
 * Tests for the MySQL axis-order option of PdoEngine.
 */
class PdoEngineMysqlAxisOrderTest extends AbstractTestCase
{
    /**
     * WGS 84: a geographic SRS whose EPSG-defined axis order is latitude-longitude.
     */
    private const SRID_WGS84 = 4326;

    public function test_long_lat_and_lat_long_produce_different_results(): void
    {
        $pdo = $this->getMysqlPdo();

        $madrid = Point::xy(-3.7, 40.4, self::SRID_WGS84);
        $paris = Point::xy(2.35, 48.85, self::SRID_WGS84);

        $longLatEngine = new PdoEngine($pdo, useProxy: false, mysqlGeoAxisOrder: MysqlGeoAxisOrder::LongLat);
        $latLongEngine = new PdoEngine($pdo, useProxy: false, mysqlGeoAxisOrder: MysqlGeoAxisOrder::LatLong);

        $longLatDistance = $longLatEngine->distance($madrid, $paris);
        $latLongDistance = $latLongEngine->distance($madrid, $paris);

        self::assertNotEqualsWithDelta($longLatDistance, $latLongDistance, 1.0);
    }

    public function test_srid_defined_matches_lat_long_for_wgs84(): void
    {
        $pdo = $this->getMysqlPdo();

        $madrid = Point::xy(-3.7, 40.4, self::SRID_WGS84);
        $paris = Point::xy(2.35, 48.85, self::SRID_WGS84);

        $latLongEngine = new PdoEngine($pdo, useProxy: false, mysqlGeoAxisOrder: MysqlGeoAxisOrder::LatLong);
        $sridDefinedEngine = new PdoEngine($pdo, useProxy: false, mysqlGeoAxisOrder: MysqlGeoAxisOrder::SridDefined);

        $latLongDistance = $latLongEngine->distance($madrid, $paris);
        $sridDefinedDistance = $sridDefinedEngine->distance($madrid, $paris);

        self::assertEqualsWithDelta($latLongDistance, $sridDefinedDistance, 0.001);
    }

    public function test_axis_order_has_no_effect_on_cartesian_srid(): void
    {
        $pdo = $this->getMysqlPdo();

        $engine = new PdoEngine($pdo, useProxy: false, mysqlGeoAxisOrder: MysqlGeoAxisOrder::LatLong);

        $point = Point::xy(10.0, 20.0);
        $centroid = $engine->centroid($point);

        self::assertPointXyEquals(10.0, 20.0, 0, $centroid);
    }

    private function getMysqlPdo(): PDO
    {
        $engine = $GLOBALS['GEOMETRY_ENGINE'] ?? null;

        if (! $engine instanceof PdoEngine) {
            self::markTestSkipped('This test requires the pdo_mysql geometry engine.');
        }

        $pdo = $engine->getPDO();

        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
            self::markTestSkipped('This test requires the pdo_mysql geometry engine.');
        }

        $version = $pdo->query('SELECT VERSION()');

        if ($version === false) {
            self::markTestSkipped('SELECT VERSION() to determine Mysql vs MariaDB failed');
        }

        $version = (string) $version->fetchColumn();

        if (strpos($version, '-MariaDB') !== false) {
            self::markTestSkipped('MariaDB does not support the axis-order option of ST_GeomFromWKB().');
        }

        return $pdo;
    }
}
