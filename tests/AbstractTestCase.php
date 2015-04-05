<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Engine\GEOSEngine;
use Brick\Geo\Engine\PDOEngine;
use Brick\Geo\Geometry;
use Brick\Geo\GeometryCollection;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiPolygon;
use Brick\Geo\Point;
use Brick\Geo\LineString;
use Brick\Geo\Polygon;

/**
 * Base class for Geometry tests.
 */
class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $name
     *
     * @return boolean
     */
    private function isPDODriver($name)
    {
        $engine = GeometryEngineRegistry::get();

        if ($engine instanceof PDOEngine) {
            if ($engine->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME) === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param boolean     $testMariaDB  False to check for MYSQL, true to check for MariaDB.
     * @param string|null $testOperator An optional comparison operator for the version number check.
     * @param string|null $testVersion  An optional version number to check.
     *
     * @return boolean
     */
    private function isMySQLorMariaDB($testMariaDB, $testOperator = null, $testVersion = null)
    {
        $engine = GeometryEngineRegistry::get();

        if ($engine instanceof PDOEngine) {
            $pdo = $engine->getPDO();

            if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql') {
                $statement = $pdo->query("SHOW VARIABLES LIKE 'version'");
                $version = $statement->fetchColumn(1);

                $isMariaDB = (substr($version, -8) === '-MariaDB');

                if ($isMariaDB) {
                    $version = substr($version, 0, -8);
                }

                if ($testVersion === null || $testOperator === null) {
                    return $testMariaDB === $isMariaDB;
                }

                return version_compare($version, $testVersion, $testOperator);
            }
        }

        return false;
    }

    /**
     * @return boolean
     */
    final protected function isMySQL()
    {
        return $this->isMySQLorMariaDB(false);
    }

    /**
     * Returns whether the tests are being run on MySQL prior to a specific version.
     *
     * @param string $version
     *
     * @return boolean
     */
    final protected function isMySQLBefore($version)
    {
        return $this->isMySQLorMariaDB(false, '<', $version);
    }

    /**
     * @return boolean
     */
    final protected function isMariaDB()
    {
        return $this->isMySQLorMariaDB(true);
    }

    /**
     * @param string $version
     *
     * @return boolean
     */
    final protected function isMariaDBAtLeast($version)
    {
        return $this->isMySQLorMariaDB(true, '>=', $version);
    }

    /**
     * @return boolean
     */
    final protected function isPostgreSQL()
    {
        return $this->isPDODriver('pgsql');
    }

    /**
     * @return boolean
     */
    final protected function isGEOS()
    {
        return GeometryEngineRegistry::get() instanceof GEOSEngine;
    }

    /**
     * @param string $message
     */
    final protected function skipMySQL($message)
    {
       if ($this->isMySQL()) {
           $this->markTestSkipped($message);
       }
    }

    /**
     * @param string $message
     */
    final protected function skipPostgreSQL($message)
    {
        if ($this->isPostgreSQL()) {
            $this->markTestSkipped($message);
        }
    }

    /**
     * @param Geometry $geometry
     */
    final protected function skipIfUnsupportedGeometry(Geometry $geometry)
    {
        if ($geometry->isEmpty() && ! $geometry instanceof GeometryCollection) {
            if ($this->isMySQL()) {
                $this->markTestSkipped('MySQL does not support empty geometries, apart from collections.');
            }
        }
    }

    /**
     * @param Geometry $geometry1
     * @param Geometry $geometry2
     * @param string   $methodName
     */
    final protected function skipIfUnsupportedByEngine(Geometry $geometry1, Geometry $geometry2, $methodName)
    {
        $this->skipIfUnsupportedGeometry($geometry1);
        $this->skipIfUnsupportedGeometry($geometry2);

        if ($this->isMySQLBefore('5.7')) {
            if ($geometry1->geometryType() !== $geometry2->geometryType()) {
                $this->markTestSkipped(sprintf('MySQL 5.6 does not support %s() on different geometry types.', $methodName));
            }
        }
    }

    /**
     * @param Geometry $geometry
     * @param string   $wkt
     * @param integer  $srid
     */
    final protected function assertWktEquals(Geometry $geometry, $wkt, $srid = 0)
    {
        $this->assertSame($wkt, $geometry->asText());
        $this->assertSame($srid, $geometry->SRID());
    }

    /**
     * Asserts that two geometries are spatially equal.
     *
     * @param Geometry $expected
     * @param Geometry $actual
     */
    final protected function assertGeometryEquals(Geometry $expected, Geometry $actual)
    {
        $expectedWKT = $expected->asText();
        $actualWKT = $actual->asText();

        if ($expectedWKT === $actualWKT) {
            // Some engines do not consider empty geometries to be equal, so we test for WKT equality first.
            $this->addToAssertionCount(1);

            return;
        }

        $this->assertTrue($actual->equals($expected), 'Failed asserting that two geometries are spatially equal.'
            . "\n---Expected"
            . "\n+++Actual"
            . "\n@@ @@"
            . "\n-" . $expectedWKT
            . "\n+" . $actualWKT
        );
    }

    /**
     * @param Geometry $g      The Geometry to test.
     * @param array    $coords The expected raw coordinates of the geometry.
     * @param boolean  $hasZ   Whether the geometry is expected to contain Z coordinates.
     * @param boolean  $hasM   Whether the geometry is expected to contain M coordinates.
     * @param integer  $srid   The expected SRID of the geometry.
     */
    final protected function assertGeometryContents(Geometry $g, array $coords, $hasZ = false, $hasM = false, $srid = 0)
    {
        $this->castToFloat($coords);

        $this->assertSame($coords, $g->toArray());
        $this->assertSame($hasZ, $g->is3D());
        $this->assertSame($hasM, $g->isMeasured());
        $this->assertSame($srid, $g->SRID());
    }

    /**
     * @param array   $coords     The expected coordinates of the Point as returned by toArray().
     * @param boolean $is3D       Whether the Point is expected to contain a Z coordinate.
     * @param boolean $isMeasured Whether the Point is expected to contain a M coordinate.
     * @param integer $srid       The expected SRID.
     * @param Point   $point      The Point to test.
     */
    final protected function assertPointEquals(array $coords, $is3D, $isMeasured, $srid, Point $point)
    {
        $this->castToFloat($coords);

        $this->assertSame($coords, $point->toArray());
        $this->assertSame($is3D, $point->is3D());
        $this->assertSame($isMeasured, $point->isMeasured());
        $this->assertSame($srid, $point->SRID());
    }

    /**
     * @param array      $coords     The expected coordinates of the LineString as returned by toArray().
     * @param boolean    $is3D       Whether the LineString is expected to contain Z coordinates.
     * @param boolean    $isMeasured Whether the LineString is expected to contain M coordinates.
     * @param LineString $lineString The LineString to test.
     */
    final protected function assertLineStringEquals(array $coords, $is3D, $isMeasured, LineString $lineString)
    {
        $this->castToFloat($coords);

        $this->assertSame($coords, $lineString->toArray());
        $this->assertSame($is3D, $lineString->is3D());
        $this->assertSame($isMeasured, $lineString->isMeasured());
    }

    /**
     * @param array   $coords     The expected coordinates of the Polygon as returned by toArray().
     * @param boolean $is3D       Whether the Polygon is expected to contain Z coordinates.
     * @param boolean $isMeasured Whether the Polygon is expected to contain M coordinates.
     * @param Polygon $polygon    The Polygon to test.
     */
    final protected function assertPolygonEquals(array $coords, $is3D, $isMeasured, Polygon $polygon)
    {
        $this->castToFloat($coords);

        $this->assertSame($coords, $polygon->toArray());
        $this->assertSame($is3D, $polygon->is3D());
        $this->assertSame($isMeasured, $polygon->isMeasured());
    }

    /**
     * @param array      $coords     The expected coordinates of the MultiPoint as returned by toArray().
     * @param boolean    $is3D       Whether the MultiPoint is expected to contain Z coordinates.
     * @param boolean    $isMeasured Whether the MultiPoint is expected to contain M coordinates.
     * @param MultiPoint $multiPoint The MultiPoint to test.
     */
    final protected function assertMultiPointEquals(array $coords, $is3D, $isMeasured, MultiPoint $multiPoint)
    {
        $this->castToFloat($coords);

        $this->assertSame($coords, $multiPoint->toArray());
        $this->assertSame($is3D, $multiPoint->is3D());
        $this->assertSame($isMeasured, $multiPoint->isMeasured());
    }

    /**
     * @param array           $coords          The expected coordinates of the MultiLineString as returned by toArray().
     * @param boolean         $is3D            Whether the MultiLineString is expected to contain Z coordinates.
     * @param boolean         $isMeasured      Whether the MultiLineString is expected to contain M coordinates.
     * @param MultiLineString $multiLineString The MultiLineString to test.
     */
    final protected function assertMultiLineStringEquals(array $coords, $is3D, $isMeasured, MultiLineString $multiLineString)
    {
        $this->castToFloat($coords);

        $this->assertSame($coords, $multiLineString->toArray());
        $this->assertSame($is3D, $multiLineString->is3D());
        $this->assertSame($isMeasured, $multiLineString->isMeasured());
    }

    /**
     * @param array        $coords       The expected coordinates of the MultiPolygon as returned by toArray().
     * @param boolean      $is3D         Whether the MultiPolygon is expected to contain Z coordinates.
     * @param boolean      $isMeasured   Whether the MultiPolygon is expected to contain M coordinates.
     * @param MultiPolygon $multiPolygon The MultiPolygon to test.
     */
    final protected function assertMultiPolygonEquals(array $coords, $is3D, $isMeasured, MultiPolygon $multiPolygon)
    {
        $this->castToFloat($coords);

        $this->assertSame($coords, $multiPolygon->toArray());
        $this->assertSame($is3D, $multiPolygon->is3D());
        $this->assertSame($isMeasured, $multiPolygon->isMeasured());
    }

    /**
     * Casts all values in the array to floats.
     *
     * This allows to write more concise data providers such as [1 2] instead of [1.0, 2.0]
     * while still strictly enforcing that the toArray() methods of the geometries return float values.
     *
     * @param array $coords
     *
     * @return void
     */
    private function castToFloat(array & $coords)
    {
        array_walk_recursive($coords, function (& $value) {
            $value = (float) $value;
        });
    }

    /**
     * @param array   $coords
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     *
     * @return Point
     */
    final protected function createPoint(array $coords, $is3D, $isMeasured, $srid = 0)
    {
        if (! $coords) {
            if ($is3D && $isMeasured) {
                return Point::xyzmEmpty($srid);
            }

            if ($is3D) {
                return Point::xyzEmpty($srid);
            }

            if ($isMeasured) {
                return Point::xymEmpty($srid);
            }

            return Point::xyEmpty($srid);
        }

        if ($is3D && $isMeasured) {
            return Point::xyzm($coords[0], $coords[1], $coords[2], $coords[3], $srid);
        }

        if ($is3D) {
            return Point::xyz($coords[0], $coords[1], $coords[2], $srid);
        }

        if ($isMeasured) {
            return Point::xym($coords[0], $coords[1], $coords[2], $srid);
        }

        return Point::xy($coords[0], $coords[1], $srid);
    }

    /**
     * @param array   $coords
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     *
     * @return LineString
     */
    final protected function createLineString(array $coords, $is3D, $isMeasured, $srid = 0)
    {
        $points = [];

        foreach ($coords as $point) {
            $points[] = self::createPoint($point, $is3D, $isMeasured, $srid);
        }

        return LineString::create($points, $is3D, $isMeasured, $srid);
    }

    /**
     * @param array   $coords
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     *
     * @return Polygon
     */
    final protected function createPolygon(array $coords, $is3D, $isMeasured, $srid = 0)
    {
        $rings = [];

        foreach ($coords as $point) {
            $rings[] = self::createLineString($point, $is3D, $isMeasured, $srid);
        }

        return Polygon::create($rings, $is3D, $isMeasured, $srid);
    }

    /**
     * @param array   $coords
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     *
     * @return MultiPoint
     */
    final protected function createMultiPoint(array $coords, $is3D, $isMeasured, $srid = 0)
    {
        $points = [];

        foreach ($coords as $point) {
            $points[] = self::createPoint($point, $is3D, $isMeasured, $srid);
        }

        return MultiPoint::create($points, $is3D, $isMeasured, $srid);
    }

    /**
     * @param array   $coords
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     *
     * @return MultiLineString
     */
    final protected function createMultiLineString(array $coords, $is3D, $isMeasured, $srid = 0)
    {
        $lineStrings = [];

        foreach ($coords as $lineString) {
            $lineStrings[] = self::createLineString($lineString, $is3D, $isMeasured, $srid);
        }

        return MultiLineString::create($lineStrings, $is3D, $isMeasured, $srid);
    }

    /**
     * @param array   $coords
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     *
     * @return MultiPolygon
     */
    final protected function createMultiPolygon(array $coords, $is3D, $isMeasured, $srid = 0)
    {
        $polygons = [];

        foreach ($coords as $polygon) {
            $polygons[] = self::createPolygon($polygon, $is3D, $isMeasured, $srid);
        }

        return MultiPolygon::create($polygons, $is3D, $isMeasured, $srid);
    }
}
