<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\CircularString;
use Brick\Geo\CompoundCurve;
use Brick\Geo\Curve;
use Brick\Geo\CurvePolygon;
use Brick\Geo\Engine\GeometryEngine;
use Brick\Geo\Engine\GeosEngine;
use Brick\Geo\Engine\GeosOpEngine;
use Brick\Geo\Engine\MariadbEngine;
use Brick\Geo\Engine\MysqlEngine;
use Brick\Geo\Engine\PostgisEngine;
use Brick\Geo\Engine\SpatialiteEngine;
use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Geometry;
use Brick\Geo\GeometryCollection;
use Brick\Geo\LineString;
use Brick\Geo\MultiCurve;
use Brick\Geo\MultiSurface;
use Brick\Geo\Point;
use Brick\Geo\Polygon;
use Brick\Geo\Surface;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for GeometryEngine implementations.
 */
class GeometryEngineTest extends AbstractTestCase
{
    /**
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param string $result    The WKT of the result geometry.
     */
    #[DataProvider('providerUnion')]
    public function testUnion(string $geometry1, string $geometry2, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);
        $result    = Geometry::fromText($result);

        $this->skipIfUnsupportedGeometry($geometry1);
        $this->skipIfUnsupportedGeometry($geometry2);

        $union = $geometryEngine->union($geometry1, $geometry2);

        if ($union->asText() === $result->asText()) {
            // GEOS does not consider POINT EMPTY to be equal to another POINT EMPTY;
            // a successful WKT comparison is a successful assertion for us here.
            $this->addToAssertionCount(1);

            return;
        }

        $this->assertGeometryEquals($result, $union);
    }

    public static function providerUnion() : array
    {
        return [
            ['POINT EMPTY', 'POINT (1 2)', 'POINT (1 2)'],
            ['POINT EMPTY', 'POINT EMPTY', 'POINT EMPTY'],
            ['POINT (1 2)', 'POINT (-2 3)', 'MULTIPOINT (1 2, -2 3)'],
            ['POLYGON ((1 2, 1 3, 4 3, 4 2, 1 2))', 'POLYGON ((2 1, 2 4, 3 4, 3 1, 2 1))', 'POLYGON ((2 1, 2 2, 1 2, 1 3, 2 3, 2 4, 3 4, 3 3, 4 3, 4 2, 3 2, 3 1, 2 1))'] ,
        ];
    }

    /**
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param string $result    The WKT of the result geometry.
     */
    #[DataProvider('providerDifference')]
    public function testDifference(string $geometry1, string $geometry2, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        // MySQL 5.6 difference() implementation is very buggy and should not be used.
        $this->failsOnMysql('< 5.7');

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);
        $result    = Geometry::fromText($result);

        $difference = $geometryEngine->difference($geometry1, $geometry2);
        $this->assertGeometryEquals($result, $difference);
    }

    public static function providerDifference() : array
    {
        return [
            ['MULTIPOINT (1 2, 3 4, 5 6)', 'MULTIPOINT (3 4)', 'MULTIPOINT (1 2, 5 6)'],
            ['POLYGON ((2 1, 2 2, 1 2, 1 3, 2 3, 2 4, 3 4, 3 3, 4 3, 4 2, 3 2, 3 1, 2 1))', 'POLYGON ((1 2, 1 3, 4 3, 4 2, 1 2))', 'MULTIPOLYGON (((2 1, 2 2, 3 2, 3 1, 2 1)), ((2 3, 2 4, 3 4, 3 3, 2 3)))'],
        ];
    }

    /**
     * @param string $geometry The WKT of the geometry to test.
     * @param string $envelope The WKT of the expected envelope.
     */
    #[DataProvider('providerEnvelope')]
    public function testEnvelope(string $geometry, string $envelope) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        if ($this->isGeosOp('< 3.12.0')) {
            self::markTestSkipped('geosop < 3.12.0 returns bogus results.');
        }

        $geometry = Geometry::fromText($geometry);
        $envelope = Geometry::fromText($envelope);

        $this->assertGeometryEquals($envelope, $geometryEngine->envelope($geometry));
    }

    public static function providerEnvelope() : array
    {
        return [
            ['LINESTRING (0 0, 1 3)', 'POLYGON ((0 0, 0 3, 1 3, 1 0, 0 0))'],
            ['POLYGON ((0 0, 0 1, 1 1, 0 0))', 'POLYGON ((0 0, 0 1, 1 1, 1 0, 0 0))'],
            ['MULTIPOINT (1 1, 2 2)', 'POLYGON ((1 1, 1 2, 2 2, 2 1, 1 1))']
        ];
    }

    /**
     * @param string $wkt    The WKT of the Curve or MultiCurve to test.
     * @param float  $length The expected length.
     */
    #[DataProvider('providerLength')]
    public function testLength(string $wkt, float $length) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        /** @var Curve|MultiCurve $geometry */
        $geometry = Geometry::fromText($wkt);
        $this->skipIfUnsupportedGeometry($geometry);

        $actualLength = $geometryEngine->length($geometry);

        self::assertEqualsWithDelta($length, $actualLength, 0.002);
    }

    public static function providerLength() : array
    {
        return [
            ['LINESTRING EMPTY', 0],
            ['LINESTRING (1 1, 2 1)', 1],
            ['LINESTRING (1 1, 1 2)', 1],
            ['LINESTRING (1 1, 2 2)', 1.414],
            ['LINESTRING (1 1, 2 2, 3 2, 3 3)', 3.414],

            ['CIRCULARSTRING (0 0, 1 1, 2 2)', 2.828],
            ['CIRCULARSTRING (0 0, 1 1, 2 1)', 2.483],
            ['CIRCULARSTRING (0 0, 1 1, 3 1, 4 3, 5 3)', 7.013],

            ['COMPOUNDCURVE ((1 1, 2 1), CIRCULARSTRING (2 1, 1 1, 2 2))', 4.331],
            ['COMPOUNDCURVE (CIRCULARSTRING (0 0, 1 1, 2 1), (2 1, 2 2, 3 2, 3 3))', 5.483],

            ['MULTILINESTRING ((1 1, 2 1))', 1],
            ['MULTILINESTRING ((1 1, 1 2))', 1],
            ['MULTILINESTRING ((1 1, 2 2))', 1.414],
            ['MULTILINESTRING ((1 1, 2 2, 3 2, 3 3))', 3.414],
            ['MULTILINESTRING ((1 1, 2 1), (2 2, 2 3))', 2],
            ['MULTILINESTRING ((1 1, 2 2), (1 1, 2 2, 3 2, 3 3))', 4.828],
        ];
    }

    /**
     * @param string $wkt The WKT of the Surface to test.
     * @param float  $area    The expected area.
     */
    #[DataProvider('providerArea')]
    public function testArea(string $wkt, float $area) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnGeosOp();

        /** @var Surface|MultiSurface $surface */
        $surface = Geometry::fromText($wkt);
        $this->skipIfUnsupportedGeometry($surface);

        $actualArea = $geometryEngine->area($surface);

        self::assertIsFloat($actualArea);
        self::assertEqualsWithDelta($area, $actualArea, 0.001);
    }

    public static function providerArea() : array
    {
        return [
            ['POLYGON ((1 1, 1 9, 9 1, 1 1))', 32],
            ['POLYGON ((1 1, 1 9, 9 1, 1 1), (2 4, 2 5, 4 5, 4 4, 2 4))', 30],
            ['POLYGON ((1 1, 1 9, 9 1, 1 1), (2 4, 2 5, 4 5, 4 4, 2 4), (2 2, 2 3, 3 3, 3 2, 2 2))', 29],

            ['POLYGON ((1 3, 3 5, 4 7, 7 3, 1 3))', 11],
            ['CURVEPOLYGON ((1 3, 3 5, 4 7, 7 3, 1 3))', 11],
            ['CURVEPOLYGON (CIRCULARSTRING (1 3, 3 5, 4 7, 7 3, 1 3))', 24.951],

            ['MULTIPOLYGON (((1 1, 1 9, 9 1, 1 1)))', 32],
            ['MULTIPOLYGON (((1 1, 1 9, 9 1, 1 1), (2 4, 2 5, 4 5, 4 4, 2 4)))', 30],
            ['MULTIPOLYGON (((1 1, 1 9, 9 1, 1 1), (2 4, 2 5, 4 5, 4 4, 2 4), (2 2, 2 3, 3 3, 3 2, 2 2)))', 29],
            ['MULTIPOLYGON (((1 1, 1 9, 9 1, 1 1), (2 4, 2 5, 4 5, 4 4, 2 4)), ((6 5, 6 9, 11 9, 11 5, 6 5)))', 50],
            ['MULTIPOLYGON Z (((1 1 0, 1 3 0, 4 3 0, 4 5 0, 6 5 0, 6 1 0, 1 1 0)), ((2 4 0, 2 6 0, 4 6 0, 2 4 0)))', 16],
        ];
    }

    /**
     * @param string     $observerWkt     The WKT of the point, representing the observer location.
     * @param string     $subjectWkt      The WKT of the point, representing the subject location.
     * @param float|null $azimuthExpected The expected azimuth, or null if an exception is expected.
     */
    #[DataProvider('providerAzimuth')]
    public function testAzimuth(string $observerWkt, string $subjectWkt, ?float $azimuthExpected): void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMysql();
        $this->failsOnMariadb();
        $this->failsOnGeos();
        $this->failsOnGeosOp();

        $observer = Point::fromText($observerWkt);
        $subject = Point::fromText($subjectWkt);

        if ($azimuthExpected === null) {
            $this->expectException(GeometryEngineException::class);
        }

        $azimuthActual = $geometryEngine->azimuth($observer, $subject);

        self::assertEqualsWithDelta($azimuthExpected, $azimuthActual, 0.001);
    }

    public static function providerAzimuth(): array
    {
        return [
            ['POINT (0 0)', 'POINT (0 0)', null],
            ['POINT (0 0)', 'POINT (0 1)', 0],
            ['POINT (0 0)', 'POINT (1 0)', pi() / 2],
            ['POINT (0 0)', 'POINT (0 -1)', pi()],
            ['POINT (0 0)', 'POINT (-1 0)', pi() * 3 / 2],
            ['POINT (0 0)', 'POINT (-0.000001 1)', pi() * 2],
        ];
    }

    /**
     * @param string        $wkt              The WKT of the geometry to calculate centroid for.
     * @param float         $centroidX        Expected `x` coordinate of the geometry centroid.
     * @param float         $centroidY        Expected `y` coordinate of the geometry centroid.
     * @param string[]|null $supportedEngines The engines that support this test, or null for all engines.
     */
    #[DataProvider('providerCentroid')]
    public function testCentroid(string $wkt, float $centroidX, float $centroidY, ?array $supportedEngines) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        if ($supportedEngines !== null) {
            $this->requireEngine($supportedEngines);
        }

        $wkt = Geometry::fromText($wkt);

        $centroid = $geometryEngine->centroid($wkt);

        $this->assertEqualsWithDelta($centroidX, $centroid->x(), 0.001);
        $this->assertEqualsWithDelta($centroidY, $centroid->y(), 0.001);
    }

    /**
     * Note: centroid() on CurvePolygon is not currently supported by any geometry engine.
     */
    public static function providerCentroid() : array
    {
        return [
            ['POINT (42 42)', 42.0, 42.0, ['MySQL', 'SpatiaLite', 'PostGIS', 'GEOS']],
            ['MULTIPOINT (0 0, 1 1)', 0.5, 0.5, ['MySQL', 'SpatiaLite', 'PostGIS', 'GEOS']],
            ['CIRCULARSTRING (1 1, 2 0, -1 1)', 0.0, -1.373, ['PostGIS']],

            ['POLYGON ((0 1, 1 0, 0 -1, -1 0, 0 1))', 0.0, 0.0, null],
            ['POLYGON ((1 2, 1 3, 2 3, 2 4, 3 4, 3 3, 4 3, 4 2, 3 2, 3 1, 2 1, 2 2, 1 2))', 2.5, 2.5, null],
            ['POLYGON ((0 0, 0 3, 3 3, 3 0, 0 0), (1 1, 1 2, 2 2, 2 1, 1 1))', 1.5, 1.5, null],

            ['MULTIPOLYGON (((0 0, 0 3, 3 3, 3 0, 0 0), (1 1, 1 2, 2 2, 2 1, 1 1)))', 1.5, 1.5, null],
            ['MULTIPOLYGON (((1 1, 1 3, 3 3, 3 1, 1 1)), ((4 1, 4 3, 6 3, 6 1, 4 1)))', 3.5, 2.0, null],
            ['MULTIPOLYGON (((1 1, 1 4, 4 4, 4 1, 1 1), (2 2, 2 3, 3 3, 3 2, 2 2)), ((5 1, 5 4, 8 4, 8 1, 5 1), (6 2, 6 3, 7 3, 7 2, 6 2)))', 4.5, 2.5, null],
        ];
    }

    /**
     * @param string $wkt The WKT of the Surface or MultiSurface to test.
     */
    #[DataProvider('providerPointOnSurface')]
    public function testPointOnSurface(string $wkt) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMysql();
        $this->failsOnMariadb('< 10.1.2');
        $this->failsOnGeosOp();

        /** @var Surface|MultiSurface $geometry */
        $geometry = Geometry::fromText($wkt);
        $this->skipIfUnsupportedGeometry($geometry);

        $pointOnSurface = $geometryEngine->pointOnSurface($geometry);

        self::assertTrue($geometryEngine->contains($geometry, $pointOnSurface));
    }

    public static function providerPointOnSurface() : array
    {
        return [
            ['POLYGON ((1 1, 1 3, 4 3, 4 6, 6 6, 6 1, 1 1))'],
            ['POLYGON ((0 0, 0 4, 3 4, 3 3, 4 3, 4 0, 0 0))'],
            ['POLYGON ((0 0, 0 3, 3 3, 3 0, 0 0), (1 1, 1 2, 2 2, 2 1, 1 1))'],

            // Note: pointOnSurface() on CurvePolygon is not currently supported by any geometry engine.

            ['MULTIPOLYGON (((1 1, 1 3, 4 3, 4 6, 6 6, 6 1, 1 1)))'],
            ['MULTIPOLYGON (((0 0, 0 4, 3 4, 3 3, 4 3, 4 0, 0 0)))'],
            ['MULTIPOLYGON (((0 0, 0 3, 3 3, 3 0, 0 0), (1 1, 1 2, 2 2, 2 1, 1 1)))'],
            ['MULTIPOLYGON (((1 1, 1 9, 9 1, 1 1), (2 4, 2 5, 4 5, 4 4, 2 4)), ((6 5, 6 9, 11 9, 11 5, 6 5)))'],
        ];
    }

    /**
     * @param string   $geometry The WKT of the geometry to test.
     * @param string[] $boundary The WKT of the expected boundary. Different engines have different outputs,
     *                           so we allow multiple possible values.
     */
    #[DataProvider('providerBoundary')]
    public function testBoundary(string $geometry, array $boundary) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry = Geometry::fromText($geometry);

        $this->skipIfUnsupportedGeometry($geometry);

        // MySQL and older MariaDB do not support boundary.
        $this->failsOnMysql();
        $this->failsOnMariadb('< 10.1.2');

        if ($geometry instanceof Point) {
            // SpatiaLite fails to return a result for a point's boundary.
            $this->failsOnSpatialite();
        }

        $actualBoundary = $geometryEngine->boundary($geometry);

        self::assertContains($actualBoundary->asText(), $boundary);
    }

    public static function providerBoundary() : array
    {
        return [
            ['POINT (1 2)', [
                'POINT EMPTY',
                'GEOMETRYCOLLECTION EMPTY',
                'POINT (1 2)', // geosop
            ]],
            ['POINT Z (2 3 4)', [
                'POINT Z EMPTY',
                'GEOMETRYCOLLECTION EMPTY',
                'POINT Z (2 3 4)', // geosop
            ]],
            ['POINT M (3 4 5)', [
                'POINT M EMPTY',
                'GEOMETRYCOLLECTION EMPTY',
                'POINT (3 4)', // geosop
            ]],
            ['POINT ZM (4 5 6 7)', [
                'POINT ZM EMPTY',
                'GEOMETRYCOLLECTION EMPTY',
                'POINT Z (4 5 6)', // geosop
            ]],
            ['LINESTRING (1 1, 0 0, -1 1)', [
                'MULTIPOINT (1 1, -1 1)',
                'POLYGON ((0 0, -1 1, 1 1, 0 0))', // geosop
            ]],
            ['POLYGON ((1 1, 0 0, -1 1, 1 1))', [
                'LINESTRING (1 1, 0 0, -1 1, 1 1)',
                'POLYGON ((0 0, -1 1, 1 1, 0 0))', // geosop
            ]],
        ];
    }

    /**
     * @param string $geometry The WKT of the geometry to test.
     * @param bool   $isValid  Whether the geometry is valid.
     */
    #[DataProvider('providerIsValid')]
    public function testIsValid(string $geometry, bool $isValid) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMysql('< 5.7.6-m16');
        $this->failsOnMariadb('>= 10.0');

        $geometry = Geometry::fromText($geometry);

        $this->skipIfUnsupportedGeometry($geometry);

        self::assertSame($isValid, $geometryEngine->isValid($geometry));
    }

    public static function providerIsValid() : array
    {
        return [
            ['POINT (1 2)', true],
            ['LINESTRING EMPTY', true],
            ['LINESTRING (1 2, 3 4)', true],
            ['POLYGON EMPTY', true],
            ['POLYGON ((0 0, 0 1, 1 1, 1 0, 0 0))', true],
            ['POLYGON ((0 0, 0 1, 1 0, 1 1, 0 0))', false],
        ];
    }

    /**
     * @param string $geometryWkt The WKT of the geometry to test.
     * @param string $validGeometryWkt The WKT of the expected geometry.
     */
    #[DataProvider('providerMakeValid')]
    public function testMakeValid(string $geometryWkt, string $validGeometryWkt) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMysql();
        $this->failsOnMariadb();
        $this->failsOnGeos();

        $geometry = Geometry::fromText($geometryWkt);
        $validGeometry = Geometry::fromText($validGeometryWkt);

        $makeValidGeometry = $geometryEngine->makeValid($geometry);

        // ensure that our tests are valid
        self::assertTrue($geometryEngine->isValid($validGeometry));
        self::assertSame($geometryWkt === $validGeometryWkt, $geometryEngine->isValid($geometry));

        if ($geometryWkt === $validGeometryWkt) {
            // valid geometries should be returned as is
            self::assertSame($geometryWkt, $makeValidGeometry->asText());
        } else {
            $this->assertGeometryEquals($validGeometry, $makeValidGeometry);
            self::assertTrue($geometryEngine->isValid($makeValidGeometry));
        }
    }

    public static function providerMakeValid() : array
    {
        return [
            // valid geometries, returned as is
            ['POINT (1 2)', 'POINT (1 2)'],
            ['LINESTRING (1 2, 3 4)', 'LINESTRING (1 2, 3 4)'],
            ['POLYGON ((0 0, 1 1, 1 0, 0 0))', 'POLYGON ((0 0, 1 1, 1 0, 0 0))'],
            ['MULTIPOLYGON (((0 0, 1 1, 1 0, 0 0)), ((2 2, 3 3, 3 2, 2 2)))', 'MULTIPOLYGON (((0 0, 1 1, 1 0, 0 0)), ((2 2, 3 3, 3 2, 2 2)))'],

            // invalid geometries
            ['MULTIPOLYGON (((0 2, 10 12, 12 10, 2 0, 0 2)), ((0 6, 6 12, 12 6, 6 0, 0 6)))', 'MULTIPOLYGON (((2 0, 0 2, 2 4, 4 2, 2 0)), ((0 6, 6 12, 8 10, 2 4, 0 6)), ((12 6, 6 0, 4 2, 10 8, 12 6)), ((8 10, 10 12, 12 10, 10 8, 8 10)))'],
            ['MULTIPOLYGON (((0 24, 4 32, 12 36, 20 32, 24 24, 20 16, 12 12, 4 16, 0 24)), ((12 24, 16 32, 24 36, 32 32, 36 24, 32 16, 24 12, 16 16, 12 24)), ((0 12, 4 20, 12 24, 20 20, 24 12, 20 4, 12 0, 4 4, 0 12)), ((12 12, 16 20, 24 24, 32 20, 36 12, 32 4, 24 0, 16 4, 12 12)))', 'MULTIPOLYGON(((0 24, 4 32, 12 36, 18 33, 16 32, 12 24, 4 20, 3 18, 0 24)), ((4 4, 0 12, 3 18, 4 16, 12 12, 16 4, 18 3, 12 0, 4 4)), ((15 18, 16 16, 18 15, 12 12, 15 18)), ((12 24, 18 21, 16 20, 15 18, 12 24)), ((20 4, 24 12, 32 16, 33 18, 36 12, 32 4, 24 0, 18 3, 20 4)), ((20 16, 21 18, 24 12, 18 15, 20 16)), ((20 20, 18 21, 24 24, 21 18, 20 20)), ((18 33, 24 36, 32 32, 36 24, 33 18, 32 20, 24 24, 20 32, 18 33)))'],
        ];
    }

    /**
     * @param string $wkt      The WKT of the Curve or MultiCurve to test.
     * @param bool   $isClosed Whether the Curve is closed.
     */
    #[DataProvider('providerIsClosed')]
    public function testIsClosed(string $wkt, bool $isClosed) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnGeosOp();

        $geometry = Geometry::fromText($wkt);
        $this->skipIfUnsupportedGeometry($geometry);

        if ($geometry instanceof MultiCurve) {
            // GEOS PHP bindings do not support isClosed() on MultiCurve in older versions.
            $this->failsOnGeos('< 3.5.0');
        }

        self::assertSame($isClosed, $geometryEngine->isClosed($geometry));
    }

    public static function providerIsClosed() : array
    {
        return [
            ['LINESTRING (1 1, 2 2)', false],
            ['LINESTRING (1 1, 2 2, 3 3)', false],
            ['LINESTRING (1 1, 2 2, 3 3, 1 1)', true],
            ['LINESTRING Z (1 1 0, 1 2 0, 2 2 0)', false],
            ['LINESTRING Z (1 1 0, 1 2 0, 2 2 0, 1 1 0)', true],
            ['LINESTRING EMPTY', false],
            ['LINESTRING Z EMPTY', false],

            ['CIRCULARSTRING EMPTY', false],
            ['CIRCULARSTRING Z EMPTY', false],
            ['CIRCULARSTRING (1 1, 1 2, 2 2, 3 1, 1 1)', true],
            ['CIRCULARSTRING (1 1, 1 2, 2 2, 3 1, 1 0)', false],

            ['COMPOUNDCURVE EMPTY', false],
            ['COMPOUNDCURVE Z EMPTY', false],
            ['COMPOUNDCURVE ((1 2, 3 4), CIRCULARSTRING (3 4, 5 6, 7 8))', false],
            ['COMPOUNDCURVE ((1 2, 3 4), CIRCULARSTRING (3 4, 5 6, 1 2))', true],

            ['MULTILINESTRING ((1 1, 2 2))', false],
            ['MULTILINESTRING ((1 1, 2 2, 3 3))', false],
            ['MULTILINESTRING ((1 1, 2 2, 3 3, 1 1))', true],
            ['MULTILINESTRING ((1 1, 2 2, 3 3, 1 1), (1 1, 2 2))', false],
            ['MULTILINESTRING ((1 1, 2 2, 3 3, 1 1), (0 0, 0 1, 1 1, 0 0))', true],
            ['MULTILINESTRING Z ((1 1 0, 1 2 0, 2 2 0))', false],
            ['MULTILINESTRING Z ((1 1 0, 1 2 0, 2 2 0, 1 1 0))', true],
            ['MULTILINESTRING Z ((1 1 0, 1 2 0, 2 2 0, 1 1 0), (1 1 0, 2 2 0, 3 3 0))', false],
            ['MULTILINESTRING Z ((1 1 0, 1 2 0, 2 2 0, 1 1 0), (1 1 1, 2 2 1, 3 3 1, 1 1 1))', true],
        ];
    }

    /**
     * @param string $geometry The WKT of the geometry to test.
     * @param bool   $isSimple Whether the geometry is simple.
     */
    #[DataProvider('providerIsSimple')]
    public function testIsSimple(string $geometry, bool $isSimple) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry = Geometry::fromText($geometry);
        $this->skipIfUnsupportedGeometry($geometry);
        self::assertSame($isSimple, $geometryEngine->isSimple($geometry));
    }

    public static function providerIsSimple() : array
    {
        return [
            ['POINT (1 2)', true],
            ['POINT Z (2 3 4)', true],
            ['POINT M (3 4 5)', true],
            ['POINT ZM (4 5 6 7)', true],
            ['LINESTRING (1 2, 3 4)', true],
            ['LINESTRING (0 0, 0 1, 1 1, 1 0)', true],
            ['LINESTRING (1 0, 1 2, 2 1, 0 1)', false],
            ['MULTIPOINT (1 2)', true],
            ['MULTIPOINT (1 3)', true],
            ['MULTIPOINT (1 2, 1 3)', true],
            ['MULTIPOINT Z (1 2 3, 2 3 4)', true],
            ['MULTIPOINT Z (1 2 3, 1 2 4)', false],
            ['MULTIPOINT M (1 2 3, 2 3 4)', true],
            ['MULTIPOINT M (1 2 3, 1 2 4)', false],
            ['MULTIPOINT ZM (1 2 3 4, 2 3 4 5)', true],
            ['MULTIPOINT ZM (1 2 3 4, 1 2 4 3)', false]
        ];
    }

    /**
     * @param string $wkt    The WKT of the Curve to test.
     * @param bool   $isRing Whether the Curve is a ring.
     */
    #[DataProvider('providerIsRing')]
    public function testIsRing(string $wkt, bool $isRing) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnGeosOp();

        $curve = Curve::fromText($wkt);
        $this->skipIfUnsupportedGeometry($curve);

        if ($geometryEngine->isClosed($curve)) {
            // A bug in MariaDB returns the wrong result.
            // @see https://mariadb.atlassian.net/browse/MDEV-7510
            $this->failsOnMariadb('< 10.1.4');
        }

        self::assertSame($isRing, $geometryEngine->isRing($curve));
    }

    public static function providerIsRing() : array
    {
        return [
            ['LINESTRING (1 1, 2 2)', false],
            ['LINESTRING (1 1, 1 2, 3 3)', false],
            ['LINESTRING (1 1, 1 2, 3 3, 1 1)', true],
            ['LINESTRING (0 0, 0 1, 1 1, 1 0, 0 0)', true],
            ['LINESTRING (0 0, 0 1, 1 0, 1 1, 0 0)', false],
            ['LINESTRING Z (1 1 0, 1 2 0, 2 2 0)', false],
            ['LINESTRING Z (1 1 0, 1 2 0, 2 2 0, 1 1 0)', true],
            ['LINESTRING Z (0 0 0, 0 1 0, 1 1 0, 1 0 0, 0 0 0)', true],
            ['LINESTRING Z (0 0 0, 0 1 0, 1 0 0, 1 1 0, 0 0 0)', false],
            ['LINESTRING M (0 0 1, 0 1 2, 1 1 3, 1 0 4, 0 0 1)', true],
            ['LINESTRING M (0 0 1, 0 1 2, 1 0 3, 1 1 4, 0 0 1)', false],
            ['LINESTRING ZM (0 0 0 1, 0 1 0 2, 1 1 0 3, 1 0 0 4, 0 0 0 1)', true],
            ['LINESTRING ZM (0 0 0 1, 0 1 0 2, 1 0 0 3, 1 1 0 4, 0 0 0 1)', false],
            ['LINESTRING EMPTY', false],
            ['LINESTRING Z EMPTY', false],
            ['LINESTRING M EMPTY', false],
            ['LINESTRING ZM EMPTY', false],

            // Note: there is currently no engine support for isSimple() on non-empty circular strings.

            ['CIRCULARSTRING EMPTY', false],
            ['CIRCULARSTRING Z EMPTY', false],
            ['CIRCULARSTRING M EMPTY', false],
            ['CIRCULARSTRING ZM EMPTY', false],

            // As a consequence, there is no support for isSimple() on non-empty compound curves.

            ['COMPOUNDCURVE EMPTY', false],
            ['COMPOUNDCURVE Z EMPTY', false],
            ['COMPOUNDCURVE M EMPTY', false],
            ['COMPOUNDCURVE ZM EMPTY', false],
        ];
    }

    /**
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $equals    Whether the geometries are spatially equal.
     */
    #[DataProvider('providerEquals')]
    public function testEquals(string $geometry1, string $geometry2, bool $equals) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnGeosOp('< 3.11.0');

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedGeometry($geometry1);
        $this->skipIfUnsupportedGeometry($geometry2);

        self::assertSame($equals, $geometryEngine->equals($geometry1, $geometry2));
    }

    public static function providerEquals() : array
    {
        return [
            ['POINT (1 2)', 'POINT (1 2)', true],
            ['POINT (1 2)', 'POINT (1 1)', false],
            ['POINT (1 2)', 'POINT(2 2)', false],
            ['POINT (1 2)', 'MULTIPOINT (1 2)', true],
            ['POINT (1 2)', 'MULTIPOINT (1 1)', false],
            ['POINT (1 2)', 'MULTIPOINT (2 2)', false],
            ['POINT (1 2)', 'LINESTRING(1 2, 1 3)', false],
            ['LINESTRING EMPTY', 'LINESTRING (1 2, 3 4)', false],
            ['LINESTRING (1 2, 3 4)', 'LINESTRING (1 2, 3 4)', true],
            ['LINESTRING (1 2, 3 4)', 'LINESTRING (3 4, 1 2)', true],
            ['LINESTRING (1 2, 3 4)', 'LINESTRING (1 2, 3 3)', false],
            ['LINESTRING (1 2, 3 4)', 'LINESTRING (1 2, 4 4)', false],
            ['POLYGON ((1 2, 1 3, 2 2, 1 2))', 'POLYGON ((1 3, 2 2, 1 2, 1 3))', true],
            ['POLYGON ((1 2, 1 3, 2 2, 1 2))', 'POLYGON ((1 3, 2 3, 1 2, 1 3))', false],
            ['POLYGON ((1 2, 1 3, 2 2, 1 2))', 'GEOMETRYCOLLECTION(POLYGON ((1 3, 2 2, 1 2, 1 3)), LINESTRING (1 2, 2 2))', true],
            ['POLYGON ((1 2, 1 3, 2 2, 1 2))', 'GEOMETRYCOLLECTION(POLYGON ((1 3, 2 2, 1 2, 1 3)), LINESTRING (1 2, 1 3))', true],
            ['POLYGON ((1 2, 1 3, 2 2, 1 2))', 'GEOMETRYCOLLECTION(POLYGON ((1 3, 2 2, 1 2, 1 3)), LINESTRING (1 2, 0 2))', false],
            ['POLYGON ((0 0, 0 2, 2 2, 2 0, 0 0))', 'GEOMETRYCOLLECTION(POLYGON ((2 2, 2 0, 0 0, 0 2, 2 2)), POINT (1 1))', true],
            ['POLYGON ((0 0, 0 2, 2 2, 2 0, 0 0))', 'GEOMETRYCOLLECTION(POLYGON ((2 2, 2 0, 0 0, 0 2, 2 2)), POINT (1 2))', true],
            ['POLYGON ((0 0, 0 2, 2 2, 2 0, 0 0))', 'GEOMETRYCOLLECTION(POLYGON ((2 2, 2 0, 0 0, 0 2, 2 2)), POINT (1 3))', false],
            ['POLYGON ((0 0, 0 4, 4 4, 4 0, 0 0), (1 1, 1 3, 3 3, 3 1, 1 1))', 'GEOMETRYCOLLECTION(POLYGON ((0 4, 4 4, 4 0, 0 0, 0 4), (3 3, 3 1, 1 1, 1 3, 3 3)), POINT (2 2))', false],
            ['POLYGON ((0 0, 0 4, 4 4, 4 0, 0 0), (1 1, 1 3, 3 3, 3 1, 1 1))', 'GEOMETRYCOLLECTION(POLYGON ((0 4, 4 4, 4 0, 0 0, 0 4), (3 3, 3 1, 1 1, 1 3, 3 3)), POINT (3 2))', true],
            ['POLYGON ((0 0, 0 4, 4 4, 4 0, 0 0), (1 1, 1 3, 3 3, 3 1, 1 1))', 'GEOMETRYCOLLECTION(POLYGON ((0 4, 4 4, 4 0, 0 0, 0 4), (3 3, 3 1, 1 1, 1 3, 3 3)), POINT (4 2))', true],
            ['POLYGON ((0 0, 0 4, 4 4, 4 0, 0 0), (1 1, 1 3, 3 3, 3 1, 1 1))', 'GEOMETRYCOLLECTION(POLYGON ((0 4, 4 4, 4 0, 0 0, 0 4), (3 3, 3 1, 1 1, 1 3, 3 3)), POINT (5 2))', false],
        ];
    }

    /**
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $disjoint  Whether the geometries are spatially disjoint.
     */
    #[DataProvider('providerDisjoint')]
    public function testDisjoint(string $geometry1, string $geometry2, bool $disjoint) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnGeosOp('< 3.12.0');

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'disjoint');

        self::assertSame($disjoint, $geometryEngine->disjoint($geometry1, $geometry2));
    }

    public static function providerDisjoint() : array
    {
        return [
            ['LINESTRING (2 1, 2 2)', 'LINESTRING (2 0, 0 2)', true],
            ['LINESTRING (0 2, 2 0)', 'LINESTRING (0 0, 2 2)', false],
            ['POLYGON ((2 2, 3 4, 6 3, 2 2))', 'POLYGON ((5 4, 5 5, 7 4, 5 4))', true],
            ['POLYGON ((2 2, 3 4, 6 3, 2 2))', 'POLYGON ((5 2, 5 5, 8 3, 5 2))', false],
            ['POLYGON ((2 2, 3 4, 6 3, 2 2))', 'LINESTRING (1 1, 3 1)', true],
            ['POLYGON ((2 2, 3 4, 6 3, 2 2))', 'LINESTRING (3 1, 5 5)', false],
        ];
    }

    /**
     * @param string $geometry1  The WKT of the first geometry.
     * @param string $geometry2  The WKT of the second geometry.
     * @param bool   $intersects Whether the geometries spatially intersect.
     */
    #[DataProvider('providerIntersects')]
    public function testIntersects(string $geometry1, string $geometry2, bool $intersects) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'intersects');

        self::assertSame($intersects, $geometryEngine->intersects($geometry1, $geometry2));
    }

    public static function providerIntersects() : array
    {
        return [
            ['LINESTRING (2 1, 2 2)', 'LINESTRING (2 0, 0 2)', false],
            ['LINESTRING (0 2, 2 0)', 'LINESTRING (0 0, 2 2)', true],
            ['POLYGON ((2 2, 3 4, 6 3, 2 2))', 'POLYGON ((5 4, 5 5, 7 4, 5 4))', false],
            ['POLYGON ((2 2, 3 4, 6 3, 2 2))', 'POLYGON ((5 2, 5 5, 8 3, 5 2))', true],
            ['POLYGON ((2 2, 3 4, 6 3, 2 2))', 'LINESTRING (1 1, 3 1)', false],
            ['POLYGON ((2 2, 3 4, 6 3, 2 2))', 'LINESTRING (3 1, 5 5)', true],
        ];
    }

    /**
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $touches   Whether the geometries spatially touch.
     */
    #[DataProvider('providerTouches')]
    public function testTouches(string $geometry1, string $geometry2, bool $touches) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnGeosOp('< 3.12.0');

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'touches');

        self::assertSame($touches, $geometryEngine->touches($geometry1, $geometry2));
    }

    public static function providerTouches() : array
    {
        return [
            ['LINESTRING (1 1, 1 3)', 'LINESTRING (1 1, 3 1)', true],
            ['LINESTRING (1 1, 1 3)', 'LINESTRING (2 1, 3 1)', false],
            ['POINT (1 1)', 'LINESTRING (0 0, 1 1, 0 2)', false],
            ['POINT (0 2)', 'LINESTRING (0 0, 1 1, 0 2)', true],
            ['POLYGON ((2 1, 2 3, 4 1, 2 1))', 'POLYGON ((3 2, 4 3, 5 2, 3 2))', true],
            ['POLYGON ((2 1, 2 3, 4 1, 2 1))', 'POLYGON ((4 2, 5 3, 6 2, 4 2))', false],
            ['POLYGON ((2 1, 2 3, 4 1, 2 1))', 'LINESTRING (1 1, 1 2)', false],
            ['POLYGON ((2 1, 2 3, 4 1, 2 1))', 'LINESTRING (1 1, 2 2)', true],
        ];
    }

    /**
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $crosses   Whether the geometries spatially cross.
     */
    #[DataProvider('providerCrosses')]
    public function testCrosses(string $geometry1, string $geometry2, bool $crosses) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnGeosOp('< 3.12.0');

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'crosses');

        self::assertSame($crosses, $geometryEngine->crosses($geometry1, $geometry2));
    }

    public static function providerCrosses() : array
    {
        return [
            ['MULTIPOINT (1 3, 2 2, 3 2)', 'LINESTRING (0 3, 1 1, 2 2, 2 0)', true],
            ['MULTIPOINT (1 3, 3 3, 3 2)', 'LINESTRING (0 3, 1 1, 2 2, 2 0)', false],
            ['MULTIPOINT (1 1, 1 3, 2 3)', 'POLYGON ((0 0, 0 3, 2 0, 0 0))', true],
            ['MULTIPOINT (2 2, 1 3, 2 3)', 'POLYGON ((0 0, 0 3, 2 0, 0 0))', false],
            ['LINESTRING (0 1, 3 2, 4 1)', 'POLYGON ((1 0, 1 2, 2 3, 2 1, 1 0))', true],
            ['LINESTRING (3 3, 3 2, 4 1)', 'POLYGON ((1 0, 1 2, 2 3, 2 1, 1 0))', false],
            ['LINESTRING (1 2, 2 1, 3 3)', 'LINESTRING (2 3, 3 1, 4 2)', true],
            ['LINESTRING (1 2, 2 1, 2 0)', 'LINESTRING (2 3, 3 1, 4 2)', false],
        ];
    }

    /**
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $within    Whether the first geometry is within the second one.
     */
    #[DataProvider('providerWithin')]
    public function testWithin(string $geometry1, string $geometry2, bool $within) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnGeosOp('< 3.12.0');

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($within, $geometryEngine->within($geometry1, $geometry2));
    }

    public static function providerWithin() : array
    {
        return [
            ['POLYGON ((2 2, 4 3, 4 2, 2 2))', 'POLYGON ((1 1, 1 3, 4 4, 6 3, 5 1, 1 1))', true],
            ['POLYGON ((3 3, 5 4, 5 3, 3 3))', 'POLYGON ((1 1, 1 3, 4 4, 6 3, 5 1, 1 1))', false],
            ['POLYGON ((1 4, 3 5, 3 4, 1 4))', 'POLYGON ((1 1, 1 3, 4 4, 6 3, 5 1, 1 1))', false],
            ['POINT (0 0)', 'POLYGON ((1 1, 1 3, 4 4, 6 3, 5 1, 1 1))', false],
            ['POINT (2 2)', 'POLYGON ((1 1, 1 3, 4 4, 6 3, 5 1, 1 1))', true],
        ];
    }

    /**
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $contains  Whether the first geometry contains the second one.
     */
    #[DataProvider('providerContains')]
    public function testContains(string $geometry1, string $geometry2, bool $contains) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($contains, $geometryEngine->contains($geometry1, $geometry2));
    }

    public static function providerContains() : array
    {
        return [
            ['POLYGON ((1 1, 1 3, 4 4, 6 3, 5 1, 1 1))', 'POLYGON ((2 2, 4 3, 4 2, 2 2))', true],
            ['POLYGON ((1 1, 1 3, 4 4, 6 3, 5 1, 1 1))', 'POLYGON ((3 3, 5 4, 5 3, 3 3))', false],
            ['POLYGON ((1 1, 1 3, 4 4, 6 3, 5 1, 1 1))', 'POLYGON ((1 4, 3 5, 3 4, 1 4))', false],
            ['POLYGON ((1 1, 1 3, 4 4, 6 3, 5 1, 1 1))', 'POINT (0 0)', false],
            ['POLYGON ((1 1, 1 3, 4 4, 6 3, 5 1, 1 1))', 'POINT (2 2)', true],
        ];
    }

    /**
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $overlaps  Whether the first geometry overlaps the second one.
     */
    #[DataProvider('providerOverlaps')]
    public function testOverlaps(string $geometry1, string $geometry2, bool $overlaps) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnGeosOp('< 3.12.0');

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($overlaps, $geometryEngine->overlaps($geometry1, $geometry2));
    }

    public static function providerOverlaps() : array
    {
        return [
            ['POLYGON ((1 2, 2 4, 3 3, 2 1, 1 2))', 'POLYGON ((2 2, 2 3, 4 2, 3 1, 2 2))', true],
            ['POLYGON ((1 2, 2 4, 4 3, 2 1, 1 2))', 'POLYGON ((2 2, 2 3, 3 3, 2 2))', false],
        ];
    }

    /**
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param string $matrix    The intersection matrix pattern.
     * @param bool   $relate    Whether the first geometry is spatially related to the second one.
     */
    #[DataProvider('providerRelate')]
    public function testRelate(string $geometry1, string $geometry2, string $matrix, bool $relate) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMysql();
        $this->failsOnMariadb('< 10.1.2');
        $this->failsOnGeosOp();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($relate, $geometryEngine->relate($geometry1, $geometry2, $matrix));
    }

    public static function providerRelate() : array
    {
        return [
            ['POLYGON ((60 160, 220 160, 220 20, 60 20, 60 160))', 'POLYGON ((60 160, 20 200, 260 200, 140 80, 60 160))', '212101212', true],
            ['POLYGON ((2 3, 8 3, 4 8, 2 3))', 'POLYGON ((-3 3, 3 3, 3 6, -3 6, -3 3))', 'T*F**F***', false],
            ['POINT (10.02 20.01)', 'POINT (10.02 20.01)', 'T*F**FFF*', true],
            ['POINT (10.02 20.01)', 'POINT (30.01 20.01)', 'T*F**FFF*', false],
        ];
    }

    /**
     * @param string $geometry The WKT of the base geometry.
     * @param float  $measure  The test measure.
     * @param string $result   The WKT of the result geometry.
     */
    #[DataProvider('providerLocateAlong')]
    public function testLocateAlong(string $geometry, float $measure, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMysql();
        $this->failsOnMariadb();
        $this->failsOnGeos();
        $this->failsOnGeosOp();

        self::assertSame($result, $geometryEngine->locateAlong(Geometry::fromText($geometry), $measure)->asText());
    }

    public static function providerLocateAlong() : array
    {
        return [
            ['MULTILINESTRING M((1 2 3, 3 4 2, 9 4 3), (1 2 3, 5 4 5))', 3.0, 'MULTIPOINT M (1 2 3, 9 4 3, 1 2 3)'],
            ['MULTIPOINT M (1 2 3, 2 3 4, 3 4 5)', 5.0, 'MULTIPOINT M (3 4 5)'],
        ];
    }

    /**
     * @param string $geometry The WKT of the geometry to test.
     * @param float  $mStart   The start measure.
     * @param float  $mEnd     The end measure.
     * @param string $result   The WKT of the second geometry.
     */
    #[DataProvider('providerLocateBetween')]
    public function testLocateBetween(string $geometry, float $mStart, float $mEnd, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMysql();
        $this->failsOnMariadb();
        $this->failsOnGeos();
        $this->failsOnGeosOp();

        self::assertSame($result, $geometryEngine->locateBetween(Geometry::fromText($geometry), $mStart, $mEnd)->asText());
    }

    public static function providerLocateBetween() : array
    {
        return [
            ['MULTIPOINT M(1 2 2)', 2.0, 5.0, 'MULTIPOINT M (1 2 2)'],
            ['MULTIPOINT M(1 2 2, 2 3 4, 3 4 5, 4 5 6, 5 6 7)', 4.0, 5.0, 'MULTIPOINT M (2 3 4, 3 4 5)'],
        ];
    }

    /**
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param float  $distance  The distance between the geometries.
     */
    #[DataProvider('providerDistance')]
    public function testDistance(string $geometry1, string $geometry2, float $distance) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertEqualsWithDelta($distance, $geometryEngine->distance($geometry1, $geometry2), 1e-6);
    }

    public static function providerDistance() : array
    {
        return [
            ['POINT(2 1)', 'LINESTRING (3 0, 3 3)', 1.0],
            ['POLYGON ((1 1, 1 3, 4 4, 6 3, 5 1, 1 1))', 'POLYGON ((1 4, 3 5, 3 4, 1 4))', 0.316227766016838],
        ];
    }

    /**
     * @param string $geometry The WKT of the base geometry.
     * @param float  $distance The distance of the buffer.
     */
    #[DataProvider('providerBuffer')]
    public function testBuffer(string $geometry, float $distance) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry = Geometry::fromText($geometry);
        $buffer = $geometryEngine->buffer($geometry, $distance);

        self::assertInstanceOf(Polygon::class, $buffer);
        self::assertTrue($geometryEngine->contains($buffer, $geometry));

        /** @var Polygon $buffer */
        $ring = $buffer->exteriorRing();

        for ($n = 1; $n <= $ring->numPoints(); $n++) {
            self::assertEqualsWithDelta($distance, $geometryEngine->distance($ring->pointN($n), $geometry), 0.001);
        }
    }

    public static function providerBuffer() : array
    {
        return [
            ['POINT (1 2)', 3.0],
            ['LINESTRING (1 1, 3 3, 5 1)', 2.0],
            ['POLYGON ((0 0, 0 1, 1 1, 1 0, 0 0))', 4.0],
        ];
    }

    /**
     * @param string $geometry The WKT of the base geometry.
     * @param string $result   The WKT of the result geometry.
     */
    #[DataProvider('providerConvexHull')]
    public function testConvexHull(string $geometry, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMysql('< 5.7.6-m16');
        $this->failsOnMariadb('< 10.1.2');

        $geometry = Geometry::fromText($geometry);
        $result   = Geometry::fromText($result);

        $this->assertGeometryEquals($result, $geometryEngine->convexHull($geometry));
    }

    public static function providerConvexHull() : array
    {
        return [
            ['LINESTRING (20 20, 30 30, 20 40, 30 50)', 'POLYGON ((20 20, 20 40, 30 50, 30 30, 20 20))'],
            ['POLYGON ((30 30, 25 35, 15 50, 35 80, 40 85, 80 90,70 75, 65 70, 55 50, 75 40, 60 30, 30 30))', 'POLYGON ((30 30, 25 35, 15 50, 35 80, 40 85, 80 90, 75 40, 60 30, 30 30))'],
            ['MULTIPOINT (20 20, 30 30, 20 40, 30 50)', 'POLYGON ((20 20, 20 40, 30 50, 30 30, 20 20))'],
        ];
    }

    #[DataProvider('providerConcaveHull')]
    public function testConcaveHull(string $geometry, float $convexity, bool $allowHoles, string $expected) : void
    {
        $this->failsOnMysql();
        $this->failsOnMariadb();
        $this->failsOnSpatialite();
        $this->failsOnGeos();
        $this->failsOnGeosOp('< 3.11.0');

        if ($allowHoles) {
            // geosop supports concaveHull, but only without holes.
            $this->failsOnGeosOp();
        }

        $geometry = Geometry::fromText($geometry);
        $expected = Geometry::fromText($expected);

        $actual = $this->getGeometryEngine()->concaveHull($geometry, $convexity, $allowHoles);
        $this->assertGeometryEquals($expected, $actual);
    }

    public static function providerConcaveHull() : array
    {
        return [
            ['MULTIPOINT (5 0, 8 9, 0 15, 10 15, 13 25, 16 15, 26 15, 18 9, 21 0, 13 6)', 0.0, false, 'POLYGON ((8 9, 0 15, 10 15, 13 25, 16 15, 26 15, 18 9, 21 0, 13 6, 5 0, 8 9))'],
            ['MULTIPOINT (5 0, 8 9, 0 15, 10 15, 13 25, 16 15, 26 15, 18 9, 21 0, 13 6)', 0.0, true, 'POLYGON ((8 9, 0 15, 10 15, 13 25, 16 15, 26 15, 18 9, 21 0, 13 6, 5 0, 8 9))'],
            ['MULTIPOINT (5 0, 8 9, 0 15, 10 15, 13 25, 16 15, 26 15, 18 9, 21 0, 13 6)', 1.0, false, 'POLYGON ((5 0, 0 15, 13 25, 26 15, 21 0, 5 0))'],
            ['MULTIPOINT (5 0, 8 9, 0 15, 10 15, 13 25, 16 15, 26 15, 18 9, 21 0, 13 6)', 1.0, true, 'POLYGON ((5 0, 0 15, 13 25, 26 15, 21 0, 5 0))'],
            ['MULTIPOINT (0 6, 1 7, 2 8, 3 9, 4 10, 5 11, 6 12, 7 11, 8 10, 9 9, 10 8, 11 7, 12 6, 11 5, 10 4, 9 3, 8 2, 7 1, 6 0, 5 1, 4 2, 3 3, 2 4, 1 5, 1 6, 2 7, 3 8, 4 9, 5 10, 6 11, 7 10, 8 9, 9 8, 10 7, 11 6, 10 5, 9 4, 8 3, 7 2, 6 1, 5 2, 4 3, 3 4, 2 5, 2 6, 3 7, 4 8, 5 9, 6 10, 7 9, 8 8, 9 7, 10 6, 9 5, 8 4, 7 3, 6 2, 5 3, 4 4, 3 5, 3 6, 4 7, 5 8, 6 9, 7 8, 8 7, 9 6, 8 5, 7 4, 6 3, 5 4, 4 5, 4 6, 6 8, 8 6, 6 4)', 0.75, true, 'POLYGON ((0 6, 1 7, 2 8, 3 9, 4 10, 5 11, 6 12, 7 11, 8 10, 9 9, 10 8, 11 7, 12 6, 11 5, 10 4, 9 3, 8 2, 7 1, 6 0, 5 1, 4 2, 3 3, 2 4, 1 5, 0 6), (6 4, 8 6, 6 8, 4 6, 6 4))'],
        ];
    }

    /**
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param string $result    The WKT of the result geometry.
     */
    #[DataProvider('providerIntersection')]
    public function testIntersection(string $geometry1, string $geometry2, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);
        $result    = Geometry::fromText($result);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'intersection');

        $this->assertGeometryEquals($result, $geometryEngine->intersection($geometry1, $geometry2));
    }

    public static function providerIntersection() : array
    {
        return [
            ['POINT (0 0)', 'LINESTRING (0 0, 0 2)', 'POINT (0 0)'],
            ['POLYGON ((1 1, 1 3, 4 4, 6 3, 5 1, 1 1))', 'POLYGON ((0 3, 6 5, 6 3, 0 3))', 'POLYGON ((1 3, 4 4, 6 3, 1 3))'],
        ];
    }

    /**
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param string $result    The WKT of the result geometry.
     */
    #[DataProvider('providerSymDifference')]
    public function testSymDifference(string $geometry1, string $geometry2, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);
        $result    = Geometry::fromText($result);

        $difference = $geometryEngine->symDifference($geometry1, $geometry2);

        $this->assertGeometryEquals($result, $difference);
    }

    public static function providerSymDifference() : array
    {
        return [
            ['POLYGON ((1 1, 1 2, 2 2, 2 4, 4 4, 4 1, 1 1))', 'POLYGON ((3 0, 3 3, 5 3, 5 0, 3 0))', 'MULTIPOLYGON (((1 1, 1 2, 2 2, 2 4, 4 4, 4 3, 3 3, 3 1, 1 1)), ((3 1, 4 1, 4 3, 5 3, 5 0, 3 0, 3 1)))'],
        ];
    }

    /**
     * @param string $geometry The WKT of the geometry to test.
     * @param float  $size     The grid size.
     * @param string $result   The WKT of the result geometry.
     */
    #[DataProvider('providerSnapToGrid')]
    public function testSnapToGrid(string $geometry, float $size, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMysql();
        $this->failsOnMariadb();
        $this->failsOnGeos();
        $this->failsOnGeosOp();

        $geometry = Geometry::fromText($geometry);
        $result   = Geometry::fromText($result);

        $snapToGrid = $geometryEngine->snapToGrid($geometry, $size);

        $this->assertGeometryEquals($result, $snapToGrid);
    }

    public static function providerSnapToGrid() : array
    {
        return [
            ['POINT (1.23 4.56)', 1.0, 'POINT (1 5)'],
            ['POINT (1.23 4.56)', 0.5, 'POINT (1.0 4.5)'],
            ['POINT (1.23 4.56)', 2, 'POINT (2 4)'],
            ['LINESTRING (1.1115678 2.123, 4.111111 3.2374897, 4.11112 3.23748667)', 0.001, 'LINESTRING (1.112 2.123, 4.111 3.237)'],
        ];
    }

    /**
     * @param string $geometry  The WKT of the geometry to test.
     * @param float  $tolerance The tolerance.
     * @param string $result    The WKT of the result geometry.
     */
    #[DataProvider('providerSimplify')]
    public function testSimplify(string$geometry, float $tolerance, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMysql('< 5.7.6-m16');
        $this->failsOnMariadb('>= 10.0');
        $this->failsOnSpatialite('< 4.1.0');
        $this->failsOnGeosOp();

        $geometry = Geometry::fromText($geometry);
        $result   = Geometry::fromText($result);

        $this->assertGeometryEquals($result, $geometryEngine->simplify($geometry, $tolerance));
    }

    public static function providerSimplify() : array
    {
        return [
            ['POLYGON ((4 0, 2 1, 1 2, 0 4, 0 6, 1 8, 2 9, 4 10, 6 10, 8 9, 9 8, 10 6, 10 4, 9 2, 8 1, 6 0, 4 0))', 1, 'POLYGON ((4 0, 1 2, 0 6, 2 9, 6 10, 9 8, 10 4, 8 1, 4 0))'],
            ['POLYGON ((4 0, 2 1, 1 2, 0 4, 0 6, 1 8, 2 9, 4 10, 6 10, 8 9, 9 8, 10 6, 10 4, 9 2, 8 1, 6 0, 4 0))', 2, 'POLYGON ((4 0, 0 6, 6 10, 10 4, 4 0))'],
        ];
    }

    /**
     * @param string $geometry1   The WKT of the first geometry.
     * @param string $geometry2   The WKT of the second geometry.
     * @param float  $maxDistance The expected value.
     */
    #[DataProvider('providerMaxDistance')]
    public function testMaxDistance(string $geometry1, string $geometry2, float $maxDistance) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMysql();
        $this->failsOnMariadb();
        $this->failsOnGeos();
        $this->failsOnGeosOp();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($maxDistance, $geometryEngine->maxDistance($geometry1, $geometry2));
    }

    public static function providerMaxDistance() : array
    {
        return [
            ['POINT (0 0)', 'LINESTRING (2 0, 0 2)', 2.0],
            ['POINT (0 0)', 'LINESTRING (1 1, 0 4)', 4.0],
            ['LINESTRING (1 1, 3 1)', 'LINESTRING (4 1, 6 1)', 5.0],
        ];
    }

    #[DataProvider('providerTransform')]
    public function testTransform(string $originalWkt, int $originalSrid, int $targetSrid, string $expectedWkt) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMariadb();
        $this->failsOnGeos();
        $this->failsOnGeosOp();

        $originalGeometry = Geometry::fromText($originalWkt, $originalSrid);
        $expectedGeometry = Geometry::fromText($expectedWkt, $targetSrid);

        if ($this->isMysql()) {
            $expectedGeometry = $expectedGeometry->swapXy();
        }

        $transformedGeometry = $geometryEngine->transform($originalGeometry, $targetSrid);

        $this->assertGeometryEqualsWithDelta($expectedGeometry, $transformedGeometry, 0.02);
    }

    public static function providerTransform() : array
    {
        return [
            ['POINT (0 0)', 2154, 4326, 'POINT (-1.36 -5.98)'],
            ['POINT (100000 100000)', 2154, 4326, 'POINT (-0.77 -5.32)'],
            ['POINT (500000 1000000)', 2154, 4326, 'POINT (1.64 0.65)'],
            ['POINT (0 0)', 2249, 4326, 'POINT (-73.66 34.24)'],
            ['POINT (100000 100000)', 2249, 4326, 'POINT (-73.34 34.52)'],
            ['POINT (500000 1000000)', 2249, 4326, 'POINT (-72.04 37)'],
            ['POINT (750000 3000000)', 2249, 4326, 'POINT (-71.16 42.47)'],
        ];
    }

    /**
     * @param string|string[] $expectedWkt
     */
    #[DataProvider('providerSplit')]
    public function testSplit(string $originalWkt, string $bladeWkt, string|array $expectedWkt) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMysql();
        $this->failsOnMariadb();
        $this->failsOnGeos();
        $this->failsOnGeosOp();

        $originalGeometry = Geometry::fromText($originalWkt);
        $bladeGeometry = Geometry::fromText($bladeWkt);

        $splitGeometry = $geometryEngine->split($originalGeometry, $bladeGeometry);

        if (is_array($expectedWkt)) {
            self::assertContains($splitGeometry->asText(), $expectedWkt);
        } else {
            $this->assertSame($expectedWkt, $splitGeometry->asText());
        }
    }

    public static function providerSplit() : array
    {
        return [
            ['LINESTRING (1 1, 3 3)', 'POINT (2 2)', [
                'MULTILINESTRING ((1 1, 2 2), (2 2, 3 3))',
                'GEOMETRYCOLLECTION (LINESTRING (1 1, 2 2), LINESTRING (2 2, 3 3))',
            ]],
            ['LINESTRING (1 1, 1 2, 2 2, 2 1, 1 1)', 'LINESTRING (0 0, 3 3)', [
                'MULTILINESTRING ((1 1, 1 2, 2 2), (2 2, 2 1, 1 1))',
                'GEOMETRYCOLLECTION (LINESTRING (1 1, 1 2, 2 2), LINESTRING (2 2, 2 1, 1 1))',
            ]],
            ['POLYGON ((1 1, 1 2, 2 2, 2 1, 1 1))', 'LINESTRING (0 0, 3 3)', [
                'MULTIPOLYGON (((1 1, 1 2, 2 2, 1 1)), ((2 2, 2 1, 1 1, 2 2)))',
                'GEOMETRYCOLLECTION (POLYGON ((1 1, 1 2, 2 2, 1 1)), POLYGON ((2 2, 2 1, 1 1, 2 2)))',
            ]],
            ['POLYGON ((1 1, 1 2, 3 2, 3 1, 1 1))', 'LINESTRING (1 1, 2 2, 3 1)', [
                'MULTIPOLYGON (((1 1, 1 2, 2 2, 1 1)), ((2 2, 3 2, 3 1, 2 2)), ((3 1, 1 1, 2 2, 3 1)))',
                'GEOMETRYCOLLECTION (POLYGON ((1 1, 1 2, 2 2, 1 1)), POLYGON ((1 1, 2 2, 3 1, 1 1)), POLYGON ((3 1, 2 2, 3 2, 3 1)))',
                'GEOMETRYCOLLECTION (POLYGON ((1 1, 1 2, 2 2, 1 1)), POLYGON ((2 2, 3 2, 3 1, 2 2)), POLYGON ((3 1, 1 1, 2 2, 3 1)))',
            ]],
        ];
    }

    #[DataProvider('providerLineInterpolatePoint')]
    public function testLineInterpolatePoint(string $originalWkt, float $fraction, string $expectedWkt) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMariadb();
        $this->failsOnGeosOp();

        $lineString = LineString::fromText($originalWkt);
        $resultGeometry = $geometryEngine->lineInterpolatePoint($lineString, $fraction);

        $this->assertSame($expectedWkt, $resultGeometry->asText());
    }

    public static function providerLineInterpolatePoint() : array
    {
        return [
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0, 'POINT (0 0)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.05, 'POINT (2 2)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.1, 'POINT (4 4)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.15, 'POINT (6 6)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.2, 'POINT (8 8)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.25, 'POINT (10 10)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.3, 'POINT (12 12)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.35, 'POINT (14 14)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.4, 'POINT (16 16)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.45, 'POINT (18 18)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.50, 'POINT (20 20)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.55, 'POINT (22 18)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.6, 'POINT (24 16)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.65, 'POINT (26 14)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.7, 'POINT (28 12)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.75, 'POINT (30 10)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.8, 'POINT (32 12)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.85, 'POINT (34 14)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.9, 'POINT (36 16)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.95, 'POINT (38 18)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 1, 'POINT (40 20)'],
        ];
    }

    #[DataProvider('providerLineInterpolatePoints')]
    public function testLineInterpolatePoints(string $originalWkt, float $fraction, string $expectedWkt) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->failsOnMariadb();
        $this->failsOnSpatialite();
        $this->failsOnGeos();
        $this->failsOnGeosOp();

        $lineString = LineString::fromText($originalWkt);
        $this->skipIfUnsupportedGeometry($lineString);

        $resultGeometry = $geometryEngine->lineInterpolatePoints($lineString, $fraction);

        $this->assertSame($expectedWkt, $resultGeometry->asText());
    }

    public static function providerLineInterpolatePoints() : array
    {
        return [
            ['LINESTRING EMPTY', 0, 'MULTIPOINT EMPTY'],
            ['LINESTRING(0 0, 10 10)', 0, 'MULTIPOINT (0 0)'],
            ['LINESTRING(0 0, 10 10)', 0.2, 'MULTIPOINT (2 2, 4 4, 6 6, 8 8, 10 10)'],
            ['LINESTRING(0 0, 10 10)', 0.3, 'MULTIPOINT (3 3, 6 6, 9 9)'],
            ['LINESTRING(0 0, 10 10)', 0.4, 'MULTIPOINT (4 4, 8 8)'],
            ['LINESTRING(0 0, 10 10)', 0.5, 'MULTIPOINT (5 5, 10 10)'],
            ['LINESTRING(0 0, 10 10)', 0.6, 'MULTIPOINT (6 6)'],
            ['LINESTRING(0 0, 10 10)', 0.7, 'MULTIPOINT (7 7)'],
            ['LINESTRING(0 0, 10 10)', 0.8, 'MULTIPOINT (8 8)'],
            ['LINESTRING(0 0, 10 10)', 0.9, 'MULTIPOINT (9 9)'],
            ['LINESTRING(0 0, 10 10)', 1, 'MULTIPOINT (10 10)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0, 'MULTIPOINT (0 0)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.15, 'MULTIPOINT (6 6, 12 12, 18 18, 24 16, 30 10, 36 16)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.2, 'MULTIPOINT (8 8, 16 16, 24 16, 32 12, 40 20)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.25, 'MULTIPOINT (10 10, 20 20, 30 10, 40 20)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.3, 'MULTIPOINT (12 12, 24 16, 36 16)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.35, 'MULTIPOINT (14 14, 28 12)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.4, 'MULTIPOINT (16 16, 32 12)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.45, 'MULTIPOINT (18 18, 36 16)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.5, 'MULTIPOINT (20 20, 40 20)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.55, 'MULTIPOINT (22 18)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.6, 'MULTIPOINT (24 16)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.65, 'MULTIPOINT (26 14)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.7, 'MULTIPOINT (28 12)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.75, 'MULTIPOINT (30 10)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.8, 'MULTIPOINT (32 12)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.85, 'MULTIPOINT (34 14)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.9, 'MULTIPOINT (36 16)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 0.95, 'MULTIPOINT (38 18)'],
            ['LINESTRING(0 0, 10 10, 20 20, 30 10, 40 20)', 1, 'MULTIPOINT (40 20)'],
        ];
    }

    private function getGeometryEngine(): GeometryEngine
    {
        if (! isset($GLOBALS['GEOMETRY_ENGINE'])) {
            self::markTestSkipped('This test requires a geometry engine to be set.');
        }

        return $GLOBALS['GEOMETRY_ENGINE'];
    }

    private function failsOnMysql(?string $operatorAndVersion = null) : void
    {
        if ($this->isMysql($operatorAndVersion)) {
            $this->expectException(GeometryEngineException::class);
        }
    }

    private function failsOnMariadb(?string $operatorAndVersion = null) : void
    {
        if ($this->isMariadb($operatorAndVersion)) {
            $this->expectException(GeometryEngineException::class);
        }
    }

    private function failsOnGeos(?string $operatorAndVersion = null) : void
    {
        if ($this->isGeos($operatorAndVersion)) {
            $this->expectException(GeometryEngineException::class);
        }
    }

    private function failsOnGeosOp(?string $operatorAndVersion = null) : void
    {
        if ($this->isGeosOp($operatorAndVersion)) {
            $this->expectException(GeometryEngineException::class);
        }
    }

    private function failsOnSpatialite(?string $operatorAndVersion = null) : void
    {
        if ($this->isSpatialite($operatorAndVersion)) {
            $this->expectException(GeometryEngineException::class);
        }
    }

    private function isMysql(?string $operatorAndVersion = null) : bool
    {
        $engine = $this->getGeometryEngine();

        if ($engine instanceof MysqlEngine) {
            if ($operatorAndVersion === null) {
                return true;
            }

            $mysqlVersion = $engine->getMysqlVersion();

            return $this->isVersion($mysqlVersion, $operatorAndVersion);
        }

        return false;
    }

    private function isMariadb(?string $operatorAndVersion = null) : bool
    {
        $engine = $this->getGeometryEngine();

        if ($engine instanceof MariadbEngine) {
            if ($operatorAndVersion === null) {
                return true;
            }

            $mariadbVersion = $engine->getMariadbVersion();

            return $this->isVersion($mariadbVersion, $operatorAndVersion);
        }

        return false;
    }

    private function isPostgis() : bool
    {
        return $this->getGeometryEngine() instanceof PostgisEngine;
    }

    private function isSpatialite(?string $operatorAndVersion = null) : bool
    {
        $engine = $this->getGeometryEngine();

        if ($engine instanceof SpatialiteEngine) {
            if ($operatorAndVersion === null) {
                return true;
            }

            $spatialiteVersion = $engine->getSpatialiteVersion();

            return $this->isVersion($spatialiteVersion, $operatorAndVersion);
        }

        return false;
    }

    private function isGeos(?string $operatorAndVersion = null) : bool
    {
        $engine = $this->getGeometryEngine();

        if ($engine instanceof GeosEngine) {
            if ($operatorAndVersion === null) {
                return true;
            }

            $version = $engine->getGeosVersion();

            return $this->isVersion($version, $operatorAndVersion);
        }

        return false;
    }

    private function isGeosOp(?string $operatorAndVersion = null) : bool
    {
        $engine = $this->getGeometryEngine();

        if ($engine instanceof GeosOpEngine) {
            if ($operatorAndVersion === null) {
                return true;
            }

            $version = $engine->getGeosOpVersion();

            return $this->isVersion($version, $operatorAndVersion);
        }

        return false;
    }

    /**
     * Skips the test if the current geometry engine does not match the requirements.
     *
     * Example: ['MySQL', 'MariaDB', 'PostGIS']
     *
     * @param list<'MySQL'|'MariaDB'|'SpatiaLite'|'PostGIS'|'GEOS'|'GeosOp'> $supportedEngines
     */
    private function requireEngine(array $supportedEngines): void
    {
        $diff = array_values(array_diff($supportedEngines, ['MySQL', 'MariaDB', 'SpatiaLite', 'PostGIS', 'GEOS', 'GeosOp']));

        if ($diff) {
            throw new LogicException("Unsupported engine: {$diff[0]}");
        }

        if (in_array('MySQL', $supportedEngines) && $this->isMysql()) {
            return;
        }

        if (in_array('MariaDB', $supportedEngines) && $this->isMariadb()) {
            return;
        }

        if (in_array('SpatiaLite', $supportedEngines) && $this->isSpatialite()) {
            return;
        }

        if (in_array('PostGIS', $supportedEngines) && $this->isPostgis()) {
            return;
        }

        if (in_array('GEOS', $supportedEngines) && $this->isGeos()) {
            return;
        }

        if (in_array('GeosOp', $supportedEngines) && $this->isGeosOp()) {
            return;
        }

        self::markTestSkipped('Not supported on this geometry engine.');
    }

    private function skipIfUnsupportedGeometry(Geometry $geometry) : void
    {
        if ($geometry->is3D() || $geometry->isMeasured()) {
            // MySQL and MariaDB do not support Z and M coordinates.
            $this->failsOnMysql();
            $this->failsOnMariadb();
        }

        if ($geometry->isMeasured()) {
            if ($this->isGeos()) {
                self::markTestSkipped('GEOS does not support M coordinates in WKB.');
            }
        }

        if ($geometry->isEmpty() && ! $geometry instanceof GeometryCollection) {
            // MySQL and MariaDB do not correctly handle empty geometries, apart from collections.
            $this->failsOnMysql();
            $this->failsOnMariadb();

            if ($this->isSpatialite()) {
                self::markTestSkipped('SpatiaLite does not correctly handle empty geometries.');
            }
        }

        if ($geometry instanceof CircularString || $geometry instanceof CompoundCurve || $geometry instanceof CurvePolygon) {
            $this->failsOnMysql();
            $this->failsOnMariadb();
            $this->failsOnSpatialite();
            $this->failsOnGeos();
            $this->failsOnGeosOp('< 3.13.0');
        }
    }

    private function skipIfUnsupportedByEngine(Geometry $geometry1, Geometry $geometry2, string $methodName) : void
    {
        $this->skipIfUnsupportedGeometry($geometry1);
        $this->skipIfUnsupportedGeometry($geometry2);

        if ($this->isMysql('< 5.7')) {
            if ($geometry1->geometryType() !== $geometry2->geometryType()) {
                self::markTestSkipped(sprintf('MySQL 5.6 does not support %s() on different geometry types.', $methodName));
            }
        }
    }

    /**
     * Asserts that two geometries are spatially equal.
     */
    private function assertGeometryEquals(Geometry $expected, Geometry $actual) : void
    {
        $expectedWkt = $expected->asText();
        $actualWkt = $actual->asText();

        if ($expectedWkt === $actualWkt) {
            // Some engines do not consider empty geometries to be equal, so we test for WKT equality first.
            $this->addToAssertionCount(1);

            return;
        }

        $debug = [
            '---Expected',
            '+++Actual',
            '@@ @@',
            '-' . $expectedWkt,
            '+' . $actualWkt,
        ];

        $debug = "\n" . implode("\n", $debug);

        self::assertSame($expected->geometryType(), $actual->geometryType(), 'Failed asserting that two geometries are of the same type.' . $debug);

        if ($this->isGeosOp('< 3.11.0')) {
            self::markTestSkipped('geosop < 3.11.0 does not support equals(), skipping spatial equality assertion.');
        }

        $geometryEngine = $this->getGeometryEngine();

        self::assertTrue($geometryEngine->equals($actual, $expected), 'Failed asserting that two geometries are spatially equal.' . $debug);
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
}
