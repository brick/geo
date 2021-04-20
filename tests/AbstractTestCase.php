<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Engine\GEOSEngine;
use Brick\Geo\Engine\PDOEngine;
use Brick\Geo\Engine\SQLite3Engine;
use Brick\Geo\CoordinateSystem;
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
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * Base class for Geometry tests.
 */
class AbstractTestCase extends TestCase
{
    /**
     * Marks the current test as requiring a geometry engine to be set.
     *
     * If no engine is set, the test will be skipped.
     */
    final protected function requiresGeometryEngine() : void
    {
        if (! GeometryEngineRegistry::has()) {
            self::markTestSkipped('This test requires a geometry engine to be set.');
        }
    }

    final protected function isMySQL(?string $operatorAndVersion = null) : bool
    {
        return $this->isMySQLorMariaDB(false, $operatorAndVersion);
    }

    final protected function isMariaDB(?string $operatorAndVersion = null) : bool
    {
        return $this->isMySQLorMariaDB(true, $operatorAndVersion);
    }

    final protected function isPostGIS() : bool
    {
        return $this->isPDODriver('pgsql');
    }

    /**
     * @param string|null $operatorAndVersion An optional version to satisfy.
     */
    final protected function isSpatiaLite(?string $operatorAndVersion = null) : bool
    {
        $engine = GeometryEngineRegistry::get();

        if ($engine instanceof SQLite3Engine) {
            if ($operatorAndVersion === null) {
                return true;
            }

            $version = $engine->getSQLite3()->querySingle('SELECT spatialite_version()');

            return $this->isVersion($version, $operatorAndVersion);
        }

        return false;
    }

    /**
     * @param string|null $operatorAndVersion An optional version to satisfy.
     */
    final protected function isGEOS(?string $operatorAndVersion = null) : bool
    {
        $engine = GeometryEngineRegistry::get();

        if ($engine instanceof GEOSEngine) {
            if ($operatorAndVersion === null) {
                return true;
            }

            $version = GEOSVersion();
            $dashPos = strpos($version, '-');

            if ($dashPos !== false) {
                $version = substr($version, 0, $dashPos);
            }

            return $this->isVersion($version, $operatorAndVersion);
        }

        return false;
    }

    /**
     * Skips the test if the current geometry engine does not match the requirements.
     *
     * Example: ['MySQL', 'MariaDB', 'PostGIS']
     *
     * Supported engines:
     *
     * - MySQL
     * - MariaDB
     * - SpatiaLite
     * - PostGIS
     * - GEOS
     *
     * @param string[] $supportedEngines
     */
    final protected function requireEngine(array $supportedEngines): void
    {
        $diff = array_values(array_diff($supportedEngines, ['MySQL', 'MariaDB', 'SpatiaLite', 'PostGIS', 'GEOS']));

        if ($diff) {
            throw new LogicException("Unsupported engine: {$diff[0]}");
        }

        if (in_array('MySQL', $supportedEngines) && $this->isMySQL()) {
            return;
        }

        if (in_array('MariaDB', $supportedEngines) && $this->isMariaDB()) {
            return;
        }

        if (in_array('SpatiaLite', $supportedEngines) && $this->isSpatiaLite()) {
            return;
        }

        if (in_array('PostGIS', $supportedEngines) && $this->isPostGIS()) {
            return;
        }

        if (in_array('GEOS', $supportedEngines) && $this->isGEOS()) {
            return;
        }

        self::markTestSkipped('Not supported on this geometry engine.');
    }

    final protected function skipIfUnsupportedGeometry(Geometry $geometry) : void
    {
        if ($geometry->is3D() || $geometry->isMeasured()) {
            if ($this->isMySQL() || $this->isMariaDB()) {
                // MySQL and MariaDB do not support Z and M coordinates.
                $this->expectException(GeometryEngineException::class);
            }
        }

        if ($geometry->isMeasured()) {
            if ($this->isGEOS()) {
                self::markTestSkipped('GEOS does not support M coordinates in WKB.');
            }
        }

        if ($geometry->isEmpty() && ! $geometry instanceof GeometryCollection) {
            if ($this->isMySQL() || $this->isMariaDB()) {
                // MySQL and MariaDB do not correctly handle empty geometries, apart from collections.
                $this->expectException(GeometryEngineException::class);
            }

            if ($this->isSpatiaLite()) {
                self::markTestSkipped('SpatiaLite does not correctly handle empty geometries.');
            }
        }

        if ($geometry instanceof CircularString || $geometry instanceof CompoundCurve || $geometry instanceof CurvePolygon) {
            if ($this->isGEOS() || $this->isSpatiaLite() || $this->isMySQL() || $this->isMariaDB()) {
                // GEOS, SpatiaLite, MySQL and MariaDB do not support these geometries.
                // Only PostGIS currently supports these.
                $this->expectException(GeometryEngineException::class);
            }
        }
    }

    final protected function skipIfUnsupportedByEngine(Geometry $geometry1, Geometry $geometry2, string $methodName) : void
    {
        $this->skipIfUnsupportedGeometry($geometry1);
        $this->skipIfUnsupportedGeometry($geometry2);

        if ($this->isMySQL('< 5.7')) {
            if ($geometry1->geometryType() !== $geometry2->geometryType()) {
                self::markTestSkipped(sprintf('MySQL 5.6 does not support %s() on different geometry types.', $methodName));
            }
        }
    }

    final protected function assertWktEquals(Geometry $geometry, string $wkt, int $srid = 0) : void
    {
        self::assertSame($wkt, $geometry->asText());
        self::assertSame($srid, $geometry->SRID());
    }

    /**
     * Asserts that two geometries are spatially equal.
     */
    final protected function assertGeometryEquals(Geometry $expected, Geometry $actual) : void
    {
        $expectedWKT = $expected->asText();
        $actualWKT = $actual->asText();

        if ($expectedWKT === $actualWKT) {
            // Some engines do not consider empty geometries to be equal, so we test for WKT equality first.
            $this->addToAssertionCount(1);

            return;
        }

        self::assertSame($expected->geometryType(), $actual->geometryType());

        self::assertTrue($actual->equals($expected), 'Failed asserting that two geometries are spatially equal.'
            . "\n---Expected"
            . "\n+++Actual"
            . "\n@@ @@"
            . "\n-" . $expectedWKT
            . "\n+" . $actualWKT
        );
    }

    /**
     * Asserts that two geometries' coordinates are equal with a given delta.
     */
    final protected function assertGeometryEqualsWithDelta(Geometry $expected, Geometry $actual, float $delta = 0.0) : void
    {
        $expectedWKT = $expected->asText();
        $actualWKT = $actual->asText();

        self::assertSame($expected->geometryType(), $actual->geometryType());

        self::assertEqualsWithDelta($expected->toArray(), $actual->toArray(), $delta,
            'Failed asserting that two geometries are equal with delta.'
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
     * @param bool     $hasZ   Whether the geometry is expected to contain Z coordinates.
     * @param bool     $hasM   Whether the geometry is expected to contain M coordinates.
     * @param int      $srid   The expected SRID of the geometry.
     */
    final protected function assertGeometryContents(Geometry $g, array $coords, bool $hasZ = false, bool $hasM = false, int $srid = 0) : void
    {
        $this->castToFloat($coords);

        self::assertSame($coords, $g->toArray());
        self::assertSame($hasZ, $g->is3D());
        self::assertSame($hasM, $g->isMeasured());
        self::assertSame($srid, $g->SRID());
    }

    /**
     * @param array   $coords     The expected coordinates of the Point as returned by toArray().
     * @param bool    $is3D       Whether the Point is expected to contain a Z coordinate.
     * @param bool    $isMeasured Whether the Point is expected to contain a M coordinate.
     * @param int     $srid       The expected SRID.
     * @param Point   $point      The Point to test.
     */
    final protected function assertPointEquals(array $coords, bool $is3D, bool $isMeasured, int $srid, Point $point) : void
    {
        $this->castToFloat($coords);

        self::assertSame($coords, $point->toArray());
        self::assertSame($is3D, $point->is3D());
        self::assertSame($isMeasured, $point->isMeasured());
        self::assertSame($srid, $point->SRID());
    }

    final protected function assertPointXYEquals(float $x, float $y, int $srid, Point $point) : void
    {
        $this->assertPointEquals([$x, $y], false, false, $srid, $point);
    }

    final protected function assertPointXYZEquals(float $x, float $y, float $z, int $srid, Point $point) : void
    {
        $this->assertPointEquals([$x, $y, $z], true, false, $srid, $point);
    }

    /**
     * @param array      $coords     The expected coordinates of the LineString as returned by toArray().
     * @param bool       $is3D       Whether the LineString is expected to contain Z coordinates.
     * @param bool       $isMeasured Whether the LineString is expected to contain M coordinates.
     * @param LineString $lineString The LineString to test.
     */
    final protected function assertLineStringEquals(array $coords, bool $is3D, bool $isMeasured, LineString $lineString) : void
    {
        $this->castToFloat($coords);

        self::assertSame($coords, $lineString->toArray());
        self::assertSame($is3D, $lineString->is3D());
        self::assertSame($isMeasured, $lineString->isMeasured());
    }

    /**
     * @param array   $coords     The expected coordinates of the Polygon as returned by toArray().
     * @param bool    $is3D       Whether the Polygon is expected to contain Z coordinates.
     * @param bool    $isMeasured Whether the Polygon is expected to contain M coordinates.
     * @param Polygon $polygon    The Polygon to test.
     */
    final protected function assertPolygonEquals(array $coords, bool $is3D, bool $isMeasured, Polygon $polygon) : void
    {
        $this->castToFloat($coords);

        self::assertSame($coords, $polygon->toArray());
        self::assertSame($is3D, $polygon->is3D());
        self::assertSame($isMeasured, $polygon->isMeasured());
    }

    /**
     * @param array      $coords     The expected coordinates of the MultiPoint as returned by toArray().
     * @param bool       $is3D       Whether the MultiPoint is expected to contain Z coordinates.
     * @param bool       $isMeasured Whether the MultiPoint is expected to contain M coordinates.
     * @param MultiPoint $multiPoint The MultiPoint to test.
     */
    final protected function assertMultiPointEquals(array $coords, bool $is3D, bool $isMeasured, MultiPoint $multiPoint) : void
    {
        $this->castToFloat($coords);

        self::assertSame($coords, $multiPoint->toArray());
        self::assertSame($is3D, $multiPoint->is3D());
        self::assertSame($isMeasured, $multiPoint->isMeasured());
    }

    /**
     * @param array           $coords          The expected coordinates of the MultiLineString as returned by toArray().
     * @param bool            $is3D            Whether the MultiLineString is expected to contain Z coordinates.
     * @param bool            $isMeasured      Whether the MultiLineString is expected to contain M coordinates.
     * @param MultiLineString $multiLineString The MultiLineString to test.
     */
    final protected function assertMultiLineStringEquals(array $coords, bool $is3D, bool $isMeasured, MultiLineString $multiLineString) : void
    {
        $this->castToFloat($coords);

        self::assertSame($coords, $multiLineString->toArray());
        self::assertSame($is3D, $multiLineString->is3D());
        self::assertSame($isMeasured, $multiLineString->isMeasured());
    }

    /**
     * @param array        $coords       The expected coordinates of the MultiPolygon as returned by toArray().
     * @param bool         $is3D         Whether the MultiPolygon is expected to contain Z coordinates.
     * @param bool         $isMeasured   Whether the MultiPolygon is expected to contain M coordinates.
     * @param MultiPolygon $multiPolygon The MultiPolygon to test.
     */
    final protected function assertMultiPolygonEquals(array $coords, bool $is3D, bool $isMeasured, MultiPolygon $multiPolygon) : void
    {
        $this->castToFloat($coords);

        self::assertSame($coords, $multiPolygon->toArray());
        self::assertSame($is3D, $multiPolygon->is3D());
        self::assertSame($isMeasured, $multiPolygon->isMeasured());
    }

    final protected function createPoint(array $coords, CoordinateSystem $cs) : Point
    {
        return new Point($cs, ...$coords);
    }

    final protected function createLineString(array $coords, CoordinateSystem $cs) : LineString
    {
        $points = [];

        foreach ($coords as $point) {
            $points[] = $this->createPoint($point, $cs);
        }

        return new LineString($cs, ...$points);
    }

    final protected function createCircularString(array $coords, CoordinateSystem $cs) : CircularString
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
     */
    final protected function createLineStringOrCircularString(array $coords, CoordinateSystem $cs) : Curve
    {
        if (count($coords) % 2 === 0) {
            return $this->createLineString($coords, $cs);
        }

        return $this->createCircularString($coords, $cs);
    }

    final protected function createCompoundCurve(array $coords, CoordinateSystem $cs) : CompoundCurve
    {
        $curves = [];

        foreach ($coords as $curve) {
            $curves[] = $this->createLineStringOrCircularString($curve, $cs);
        }

        return new CompoundCurve($cs, ...$curves);
    }

    final protected function createPolygon(array $coords, CoordinateSystem $cs) : Polygon
    {
        $rings = [];

        foreach ($coords as $ring) {
            $rings[] = $this->createLineString($ring, $cs);
        }

        return new Polygon($cs, ...$rings);
    }

    final protected function createTriangle(array $coords, CoordinateSystem $cs) : Triangle
    {
        $rings = [];

        foreach ($coords as $ring) {
            $rings[] = $this->createLineString($ring, $cs);
        }

        return new Triangle($cs, ...$rings);
    }

    final protected function createCurvePolygon(array $coords, CoordinateSystem $cs) : CurvePolygon
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

    final protected function createMultiPoint(array $coords, CoordinateSystem $cs) : MultiPoint
    {
        $points = [];

        foreach ($coords as $point) {
            $points[] = $this->createPoint($point, $cs);
        }

        return new MultiPoint($cs, ...$points);
    }

    final protected function createMultiLineString(array $coords, CoordinateSystem $cs) : MultiLineString
    {
        $lineStrings = [];

        foreach ($coords as $lineString) {
            $lineStrings[] = $this->createLineString($lineString, $cs);
        }

        return new MultiLineString($cs, ...$lineStrings);
    }

    final protected function createMultiPolygon(array $coords, CoordinateSystem $cs) : MultiPolygon
    {
        $polygons = [];

        foreach ($coords as $polygon) {
            $polygons[] = $this->createPolygon($polygon, $cs);
        }

        return new MultiPolygon($cs, ...$polygons);
    }

    final protected function createPolyhedralSurface(array $coords, CoordinateSystem $cs) : PolyhedralSurface
    {
        $patches = [];

        foreach ($coords as $patch) {
            $patches[] = $this->createPolygon($patch, $cs);
        }

        return new PolyhedralSurface($cs, ...$patches);
    }

    final protected function createTIN(array $coords, CoordinateSystem $cs) : TIN
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
     */
    final protected function castToFloat(array & $coords) : void
    {
        array_walk_recursive($coords, function (& $value) {
            $value = (float) $value;
        });
    }

    /**
     * @param string $version            The version of the software in use, such as "4.0.1".
     * @param string $operatorAndVersion The comparison operator and version to test against, such as ">= 4.0".
     */
    private function isVersion(string $version, string $operatorAndVersion) : bool
    {
        if (preg_match('/^([\<\>]?\=?) ?(.*)/', $operatorAndVersion, $matches) !== 1) {
            throw new LogicException("Invalid operator and version: $operatorAndVersion");
        }

        [, $operator, $testVersion] = $matches;

        if ($operator === '') {
            $operator = '=';
        }

        return version_compare($version, $testVersion, $operator);
    }

    private function isPDODriver(string $name) : bool
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
     * @param bool        $testMariaDB        False to check for MYSQL, true to check for MariaDB.
     * @param string|null $operatorAndVersion An optional comparison operator and version number to test against.
     */
    private function isMySQLorMariaDB(bool $testMariaDB, ?string $operatorAndVersion = null) : bool
    {
        $engine = GeometryEngineRegistry::get();

        if ($engine instanceof PDOEngine) {
            $pdo = $engine->getPDO();

            if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql') {
                $statement = $pdo->query('SELECT VERSION()');
                $version = $statement->fetchColumn();

                $pos = strpos($version, '-MariaDB');
                $isMariaDB = ($pos !== false);

                if ($isMariaDB) {
                    $version = substr($version, 0, $pos);
                }

                if ($testMariaDB !== $isMariaDB) {
                    return false;
                }

                if ($operatorAndVersion === null) {
                    return true;
                }

                return $this->isVersion($version, $operatorAndVersion);
            }
        }

        return false;
    }
}
