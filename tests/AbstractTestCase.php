<?php

namespace Brick\Geo\Tests;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Engine\GEOSEngine;
use Brick\Geo\Engine\PDOEngine;
use Brick\Geo\Engine\SQLite3Engine;
use Brick\Geo\Exception\GeometryException;
use Brick\Geo\Geometry;
use Brick\Geo\GeometryCollection;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiPolygon;
use Brick\Geo\Point;
use Brick\Geo\Curve;
use Brick\Geo\LineString;
use Brick\Geo\CircularString;
use Brick\Geo\CompoundCurve;
use Brick\Geo\Polygon;
use Brick\Geo\CurvePolygon;
use Brick\Geo\PolyhedralSurface;
use Brick\Geo\TIN;
use Brick\Geo\Triangle;

/**
 * Base class for Geometry tests.
 */
class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Marks the current test as requiring a geometry engine to be set.
     *
     * If no engine is set, the test will be skipped.
     *
     * @return void
     */
    final protected function requiresGeometryEngine()
    {
        if (! GeometryEngineRegistry::has()) {
            $this->markTestSkipped('This test requires a geometry engine to be set.');
        }
    }

    /**
     * @param string|null $operatorAndVersion
     *
     * @return boolean
     */
    final protected function isMySQL($operatorAndVersion = null)
    {
        return $this->isMySQLorMariaDB(false, $operatorAndVersion);
    }

    /**
     * @param string|null $operatorAndVersion
     *
     * @return bool
     */
    final protected function isMariaDB($operatorAndVersion = null)
    {
        return $this->isMySQLorMariaDB(true, $operatorAndVersion);
    }

    /**
     * @return boolean
     */
    final protected function isPostGIS()
    {
        return $this->isPDODriver('pgsql');
    }

    /**
     * @param string|null $operatorAndVersion An optional version to satisfy.
     *
     * @return boolean
     */
    final protected function isSpatiaLite($operatorAndVersion = null)
    {
        $engine = GeometryEngineRegistry::get();

        if ($engine instanceof SQLite3Engine) {
            if ($operatorAndVersion === null) {
                return true;
            }

            $version =  $engine->getSQLite3()->querySingle('SELECT spatialite_version()');

            return $this->isVersion($version, $operatorAndVersion);
        }

        return false;
    }

    /**
     * @param string|null $operatorAndVersion An optional version to satisfy.
     *
     * @return boolean
     */
    final protected function isGEOS($operatorAndVersion = null)
    {
        $engine = GeometryEngineRegistry::get();

        if ($engine instanceof GEOSEngine) {
            if ($operatorAndVersion === null) {
                return true;
            }

            return $this->isVersion(GEOSVersion(), $operatorAndVersion);
        }

        return false;
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
    final protected function skipPostGIS($message)
    {
        if ($this->isPostGIS()) {
            $this->markTestSkipped($message);
        }
    }

    /**
     * @param Geometry $geometry
     */
    final protected function skipIfUnsupportedGeometry(Geometry $geometry)
    {
        if ($geometry->is3D() || $geometry->isMeasured()) {
            if ($this->isMySQL() || $this->isMariaDB()) {
                // MySQL and MariaDB do not support Z and M coordinates.
                $this->setExpectedException(GeometryException::class);
            }
        }

        if ($geometry->isMeasured()) {
            if ($this->isGEOS()) {
                $this->markTestSkipped('GEOS does not support M coordinates in WKB.');
            }
        }

        if ($geometry->isEmpty() && ! $geometry instanceof GeometryCollection) {
            if ($this->isMySQL() || $this->isMariaDB()) {
                // MySQL and MariaDB do not correctly handle empty geometries, apart from collections.
                $this->setExpectedException(GeometryException::class);
            }

            if ($this->isSpatiaLite()) {
                $this->markTestSkipped('SpatiaLite does not correctly handle empty geometries.');
            }
        }

        if ($geometry instanceof CircularString || $geometry instanceof CompoundCurve || $geometry instanceof CurvePolygon) {
            if ($this->isGEOS() || $this->isSpatiaLite() || $this->isMySQL() || $this->isMariaDB()) {
                // GEOS, SpatiaLite, MySQL and MariaDB do not support these geometries.
                // Only PostGIS currently supports these.
                $this->setExpectedException(GeometryException::class);
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

        if ($this->isMySQL('< 5.7')) {
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
     * @param array            $coords
     * @param CoordinateSystem $cs
     *
     * @return Point
     */
    final protected function createPoint(array $coords, CoordinateSystem $cs)
    {
        return new Point($cs, ...$coords);
    }

    /**
     * @param array            $coords
     * @param CoordinateSystem $cs
     *
     * @return LineString
     */
    final protected function createLineString(array $coords, CoordinateSystem $cs)
    {
        $points = [];

        foreach ($coords as $point) {
            $points[] = $this->createPoint($point, $cs);
        }

        return new LineString($cs, ...$points);
    }

    /**
     * @param array            $coords
     * @param CoordinateSystem $cs
     *
     * @return CircularString
     */
    final protected function createCircularString(array $coords, CoordinateSystem $cs)
    {
        $points = [];

        foreach ($coords as $point) {
            $points[] = $this->createPoint($point,$cs);
        }

        return new CircularString($cs, ...$points);
    }

    /**
     * Creates a LineString or CircularString from an array of coordinates.
     *
     * For the purpose of these tests, it is assumed that a curve with an even number of points is
     * a LineString, and a curve with an odd number of points is a CircularString.
     *
     * @param array            $coords
     * @param CoordinateSystem $cs
     *
     * @return Curve
     */
    final protected function createLineStringOrCircularString(array $coords, CoordinateSystem $cs)
    {
        if (count($coords) % 2 == 0) {
            return $this->createLineString($coords, $cs);
        } else {
            return $this->createCircularString($coords, $cs);
        }
    }

    /**
     * @param array            $coords
     * @param CoordinateSystem $cs
     *
     * @return CompoundCurve
     */
    final protected function createCompoundCurve(array $coords, CoordinateSystem $cs)
    {
        $curves = [];

        foreach ($coords as $curve) {
            $curves[] = $this->createLineStringOrCircularString($curve, $cs);
        }

        return new CompoundCurve($cs, ...$curves);
    }

    /**
     * @param array            $coords
     * @param CoordinateSystem $cs
     *
     * @return Polygon
     */
    final protected function createPolygon(array $coords, CoordinateSystem $cs)
    {
        $rings = [];

        foreach ($coords as $ring) {
            $rings[] = $this->createLineString($ring, $cs);
        }

        return new Polygon($cs, ...$rings);
    }

    /**
     * @param array            $coords
     * @param CoordinateSystem $cs
     *
     * @return Triangle
     */
    final protected function createTriangle(array $coords, CoordinateSystem $cs)
    {
        $rings = [];

        foreach ($coords as $ring) {
            $rings[] = $this->createLineString($ring, $cs);
        }

        return new Triangle($cs, ...$rings);
    }

    /**
     * @param array            $coords
     * @param CoordinateSystem $cs
     *
     * @return CurvePolygon
     */
    final protected function createCurvePolygon(array $coords, CoordinateSystem $cs)
    {
        $rings = [];

        foreach ($coords as $ring) {
            if (is_array($ring[0][0])) {
                // CompoundCurve
                $rings[] = $this->createCompoundCurve($ring, $cs);
            } else {
                // LineString or CircularString
                $rings[] = $this->createLineStringOrCircularString($ring, $cs);
            }
        }

        return new CurvePolygon($cs, ...$rings);
    }

    /**
     * @param array            $coords
     * @param CoordinateSystem $cs
     *
     * @return MultiPoint
     */
    final protected function createMultiPoint(array $coords, CoordinateSystem $cs)
    {
        $points = [];

        foreach ($coords as $point) {
            $points[] = $this->createPoint($point, $cs);
        }

        return new MultiPoint($cs, ...$points);
    }

    /**
     * @param array            $coords
     * @param CoordinateSystem $cs
     *
     * @return MultiLineString
     */
    final protected function createMultiLineString(array $coords, CoordinateSystem $cs)
    {
        $lineStrings = [];

        foreach ($coords as $lineString) {
            $lineStrings[] = $this->createLineString($lineString, $cs);
        }

        return new MultiLineString($cs, ...$lineStrings);
    }

    /**
     * @param array            $coords
     * @param CoordinateSystem $cs
     *
     * @return MultiPolygon
     */
    final protected function createMultiPolygon(array $coords, CoordinateSystem $cs)
    {
        $polygons = [];

        foreach ($coords as $polygon) {
            $polygons[] = $this->createPolygon($polygon, $cs);
        }

        return new MultiPolygon($cs, ...$polygons);
    }

    /**
     * @param array            $coords
     * @param CoordinateSystem $cs
     *
     * @return PolyhedralSurface
     */
    final protected function createPolyhedralSurface(array $coords, CoordinateSystem $cs)
    {
        $patches = [];

        foreach ($coords as $patch) {
            $patches[] = $this->createPolygon($patch, $cs);
        }

        return new PolyhedralSurface($cs, ...$patches);
    }

    /**
     * @param array            $coords
     * @param CoordinateSystem $cs
     *
     * @return TIN
     */
    final protected function createTIN(array $coords, CoordinateSystem $cs)
    {
        $patches = [];

        foreach ($coords as $patch) {
            $patches[] = $this->createTriangle($patch, $cs);
        }

        return new TIN($cs, ...$patches);
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
    final protected function castToFloat(array & $coords)
    {
        array_walk_recursive($coords, function (& $value) {
            $value = (float) $value;
        });
    }

    /**
     * @param string $version            The version of the software in use, such as "4.0.1".
     * @param string $operatorAndVersion The comparison operator and version to test against, such as ">= 4.0".
     *
     * @return boolean
     */
    private function isVersion($version, $operatorAndVersion)
    {
        preg_match('/^([\<\>]?\=?) ?(.*)/', $operatorAndVersion, $matches);
        list (, $operator, $testVersion) = $matches;

        if ($operator === '') {
            $operator = '=';
        }

        return version_compare($version, $testVersion, $operator);
    }

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
     * @param boolean     $testMariaDB        False to check for MYSQL, true to check for MariaDB.
     * @param string|null $operatorAndVersion An optional comparison operator and version number to test against.
     *
     * @return boolean
     */
    private function isMySQLorMariaDB($testMariaDB, $operatorAndVersion = null)
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

                if ($operatorAndVersion === null) {
                    return $testMariaDB === $isMariaDB;
                }

                return $this->isVersion($version, $operatorAndVersion);
            }
        }

        return false;
    }
}
