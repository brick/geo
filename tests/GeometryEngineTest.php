<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\CircularString;
use Brick\Geo\CompoundCurve;
use Brick\Geo\Curve;
use Brick\Geo\CurvePolygon;
use Brick\Geo\Engine\GeometryEngine;
use Brick\Geo\Engine\GEOSEngine;
use Brick\Geo\Engine\PDOEngine;
use Brick\Geo\Engine\SQLite3Engine;
use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Geometry;
use Brick\Geo\GeometryCollection;
use Brick\Geo\MultiCurve;
use Brick\Geo\MultiSurface;
use Brick\Geo\Point;
use Brick\Geo\Polygon;
use Brick\Geo\Surface;
use LogicException;

/**
 * Tests for GeometryEngine implementations.
 */
class GeometryEngineTest extends AbstractTestCase
{
    /**
     * @dataProvider providerUnion
     *
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param string $result    The WKT of the result geometry.
     */
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

        self::assertSame($result->geometryType(), $union->geometryType());
        self::assertTrue($geometryEngine->equals($union, $result));
    }

    public function providerUnion() : array
    {
        return [
            ['POINT EMPTY', 'POINT (1 2)', 'POINT (1 2)'],
            ['POINT EMPTY', 'POINT EMPTY', 'POINT EMPTY'],
            ['POINT (1 2)', 'POINT (-2 3)', 'MULTIPOINT (1 2, -2 3)'],
            ['POLYGON ((1 2, 1 3, 4 3, 4 2, 1 2))', 'POLYGON ((2 1, 2 4, 3 4, 3 1, 2 1))', 'POLYGON ((2 1, 2 2, 1 2, 1 3, 2 3, 2 4, 3 4, 3 3, 4 3, 4 2, 3 2, 3 1, 2 1))'] ,
        ];
    }

    /**
     * @dataProvider providerDifference
     *
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param string $result    The WKT of the result geometry.
     */
    public function testDifference(string $geometry1, string $geometry2, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        if ($this->isMySQL('< 5.7')) {
            self::markTestSkipped('MySQL 5.6 difference() implementation is very buggy and should not be used.');
        }

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);
        $result    = Geometry::fromText($result);

        $difference = $geometryEngine->difference($geometry1, $geometry2);
        $this->assertGeometryEquals($result, $difference);
    }

    public function providerDifference() : array
    {
        return [
            ['MULTIPOINT (1 2, 3 4, 5 6)', 'MULTIPOINT (3 4)', 'MULTIPOINT (1 2, 5 6)'],
            ['POLYGON ((2 1, 2 2, 1 2, 1 3, 2 3, 2 4, 3 4, 3 3, 4 3, 4 2, 3 2, 3 1, 2 1))', 'POLYGON ((1 2, 1 3, 4 3, 4 2, 1 2))', 'MULTIPOLYGON (((2 1, 2 2, 3 2, 3 1, 2 1)), ((2 3, 2 4, 3 4, 3 3, 2 3)))'],
        ];
    }

    /**
     * @dataProvider providerEnvelope
     *
     * @param string $geometry The WKT of the geometry to test.
     * @param string $envelope The WKT of the expected envelope.
     */
    public function testEnvelope(string $geometry, string $envelope) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry = Geometry::fromText($geometry);
        $envelope = Geometry::fromText($envelope);

        $this->assertGeometryEquals($envelope, $geometryEngine->envelope($geometry));
    }

    public function providerEnvelope() : array
    {
        return [
            ['LINESTRING (0 0, 1 3)', 'POLYGON ((0 0, 0 3, 1 3, 1 0, 0 0))'],
            ['POLYGON ((0 0, 0 1, 1 1, 0 0))', 'POLYGON ((0 0, 0 1, 1 1, 1 0, 0 0))'],
            ['MULTIPOINT (1 1, 2 2)', 'POLYGON ((1 1, 1 2, 2 2, 2 1, 1 1))']
        ];
    }

    /**
     * @dataProvider providerLength
     *
     * @param string $wkt    The WKT of the Curve or MultiCurve to test.
     * @param float  $length The expected length.
     */
    public function testLength(string $wkt, float $length) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        /** @var Curve|MultiCurve $geometry */
        $geometry = Geometry::fromText($wkt);
        $this->skipIfUnsupportedGeometry($geometry);

        $actualLength = $geometryEngine->length($geometry);

        self::assertEqualsWithDelta($length, $actualLength, 0.002);
    }

    public function providerLength() : array
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
     * @dataProvider providerArea
     *
     * @param string $wkt The WKT of the Surface to test.
     * @param float  $area    The expected area.
     */
    public function testArea(string $wkt, float $area) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        /** @var Surface|MultiSurface $surface */
        $surface = Geometry::fromText($wkt);
        $this->skipIfUnsupportedGeometry($surface);

        $actualArea = $geometryEngine->area($surface);

        self::assertIsFloat($actualArea);
        self::assertEqualsWithDelta($area, $actualArea, 0.001);
    }

    public function providerArea() : array
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
     * @dataProvider providerAzimuth
     *
     * @param string     $observerWkt     The WKT of the point, representing the observer location.
     * @param string     $subjectWkt      The WKT of the point, representing the subject location.
     * @param float|null $azimuthExpected The expected azimuth, or null if an exception is expected.
     */
    public function testAzimuth(string $observerWkt, string $subjectWkt, ?float $azimuthExpected): void
    {
        $geometryEngine = $this->getGeometryEngine();

        if (! $this->isPostGIS()) {
            $this->expectException(GeometryEngineException::class);
        }

        $observer = Point::fromText($observerWkt);
        $subject = Point::fromText($subjectWkt);

        if ($azimuthExpected === null) {
            $this->expectException(GeometryEngineException::class);
        }

        $azimuthActual = $geometryEngine->azimuth($observer, $subject);

        self::assertEqualsWithDelta($azimuthExpected, $azimuthActual, 0.001);
    }

    public function providerAzimuth(): array
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
     * @dataProvider providerCentroid
     *
     * @param string        $wkt              The WKT of the geometry to calculate centroid for.
     * @param float         $centroidX        Expected `x` coordinate of the geometry centroid.
     * @param float         $centroidY        Expected `y` coordinate of the geometry centroid.
     * @param string[]|null $supportedEngines The engines that support this test, or null for all engines.
     */
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
    public function providerCentroid() : array
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
     * @dataProvider providerPointOnSurface
     *
     * @param string $wkt The WKT of the Surface or MultiSurface to test.
     */
    public function testPointOnSurface(string $wkt) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        if ($this->isMySQL() || $this->isMariaDB('< 10.1.2')) {
            // MySQL and older MariaDB do not support ST_PointOnSurface()
            $this->expectException(GeometryEngineException::class);
        }

        /** @var Surface|MultiSurface $geometry */
        $geometry = Geometry::fromText($wkt);
        $this->skipIfUnsupportedGeometry($geometry);

        $pointOnSurface = $geometryEngine->pointOnSurface($geometry);

        self::assertTrue($geometryEngine->contains($geometry, $pointOnSurface));
    }

    public function providerPointOnSurface() : array
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
     * @dataProvider providerBoundary
     *
     * @param string $geometry The WKT of the geometry to test.
     * @param string $boundary The WKT of the expected boundary.
     */
    public function testBoundary(string $geometry, string $boundary) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry = Geometry::fromText($geometry);

        $this->skipIfUnsupportedGeometry($geometry);

        if ($this->isMySQL() || $this->isMariaDB('< 10.1.2')) {
            // MySQL and older MariaDB do not support boundary.
            $this->expectException(GeometryEngineException::class);
        }

        if ($this->isSpatiaLite() && $geometry instanceof Point) {
            // SpatiaLite fails to return a result for a point's boundary.
            $this->expectException(GeometryEngineException::class);
        }

        self::assertSame($boundary, $geometryEngine->boundary($geometry)->asText());
    }

    public function providerBoundary() : array
    {
        return [
            ['POINT (1 2)', 'GEOMETRYCOLLECTION EMPTY'],
            ['POINT Z (2 3 4)', 'GEOMETRYCOLLECTION EMPTY'],
            ['POINT M (3 4 5)', 'GEOMETRYCOLLECTION EMPTY'],
            ['POINT ZM (4 5 6 7)', 'GEOMETRYCOLLECTION EMPTY'],
            ['LINESTRING (1 1, 0 0, -1 1)', 'MULTIPOINT (1 1, -1 1)'],
            ['POLYGON ((1 1, 0 0, -1 1, 1 1))', 'LINESTRING (1 1, 0 0, -1 1, 1 1)'],
        ];
    }

    /**
     * @dataProvider providerIsValid
     *
     * @param string $geometry The WKT of the geometry to test.
     * @param bool   $isValid  Whether the geometry is valid.
     */
    public function testIsValid(string $geometry, bool $isValid) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        if ($this->isMySQL('< 5.7.6-m16') || $this->isMariaDB('>= 10.0')) {
            $this->expectException(GeometryEngineException::class);
        }

        $geometry = Geometry::fromText($geometry);

        $this->skipIfUnsupportedGeometry($geometry);

        self::assertSame($isValid, $geometryEngine->isValid($geometry));
    }

    public function providerIsValid() : array
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
     * @dataProvider providerMakeValid
     *
     * @param string $geometryWKT The WKT of the geometry to test.
     * @param string $validGeometryWKT The WKT of the expected geometry.
     */
    public function testMakeValid(string $geometryWKT, string $validGeometryWKT) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->requireEngine(['SpatiaLite', 'PostGIS']);

        $geometry = Geometry::fromText($geometryWKT);
        $validGeometry = Geometry::fromText($validGeometryWKT);

        $makeValidGeometry = $geometryEngine->makeValid($geometry);

        // ensure that our tests are valid
        self::assertTrue($geometryEngine->isValid($validGeometry));
        self::assertSame($geometryWKT === $validGeometryWKT, $geometryEngine->isValid($geometry));

        if ($geometryWKT === $validGeometryWKT) {
            // valid geometries should be returned as is
            self::assertSame($geometryWKT, $makeValidGeometry->asText());
        } else {
            $this->assertGeometryEquals($validGeometry, $makeValidGeometry);
            self::assertTrue($geometryEngine->isValid($makeValidGeometry));
        }
    }

    public function providerMakeValid() : array
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
     * @dataProvider providerIsClosed
     *
     * @param string $wkt      The WKT of the Curve or MultiCurve to test.
     * @param bool   $isClosed Whether the Curve is closed.
     */
    public function testIsClosed(string $wkt, bool $isClosed) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry = Geometry::fromText($wkt);
        $this->skipIfUnsupportedGeometry($geometry);

        if ($geometry instanceof MultiCurve && $this->isGEOS('< 3.5.0')) {
            // GEOS PHP bindings do not support isClosed() on MultiCurve in older versions.
            $this->expectException(GeometryEngineException::class);
        }

        self::assertSame($isClosed, $geometryEngine->isClosed($geometry));
    }

    public function providerIsClosed() : array
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
     * @dataProvider providerIsSimple
     *
     * @param string $geometry The WKT of the geometry to test.
     * @param bool   $isSimple Whether the geometry is simple.
     */
    public function testIsSimple(string $geometry, bool $isSimple) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry = Geometry::fromText($geometry);
        $this->skipIfUnsupportedGeometry($geometry);
        self::assertSame($isSimple, $geometryEngine->isSimple($geometry));
    }

    public function providerIsSimple() : array
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
     * @dataProvider providerIsRing
     *
     * @param string $wkt    The WKT of the Curve to test.
     * @param bool   $isRing Whether the Curve is a ring.
     */
    public function testIsRing(string $wkt, bool $isRing) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $curve = Curve::fromText($wkt);
        $this->skipIfUnsupportedGeometry($curve);

        if ($geometryEngine->isClosed($curve) && $this->isMariaDB('< 10.1.4')) {
            // @see https://mariadb.atlassian.net/browse/MDEV-7510
            self::markTestSkipped('A bug in MariaDB returns the wrong result.');
        }

        self::assertSame($isRing, $geometryEngine->isRing($curve));
    }

    public function providerIsRing() : array
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
     * @dataProvider providerEquals
     *
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $equals    Whether the geometries are spatially equal.
     */
    public function testEquals(string $geometry1, string $geometry2, bool $equals) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedGeometry($geometry1);
        $this->skipIfUnsupportedGeometry($geometry2);

        self::assertSame($equals, $geometryEngine->equals($geometry1, $geometry2));
    }

    public function providerEquals() : array
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
            ['POLYGON ((1 2, 1 3, 2 2, 1 2))', 'POLYGON ((1 3, 2 2, 1 2, 1 3))', true]
        ];
    }

    /**
     * @dataProvider providerDisjoint
     *
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $disjoint  Whether the geometries are spatially disjoint.
     */
    public function testDisjoint(string $geometry1, string $geometry2, bool $disjoint) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'disjoint');

        self::assertSame($disjoint, $geometryEngine->disjoint($geometry1, $geometry2));
    }

    public function providerDisjoint() : array
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
     * @dataProvider providerIntersects
     *
     * @param string $geometry1  The WKT of the first geometry.
     * @param string $geometry2  The WKT of the second geometry.
     * @param bool   $intersects Whether the geometries spatially intersect.
     */
    public function testIntersects(string $geometry1, string $geometry2, bool $intersects) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'intersects');

        self::assertSame($intersects, $geometryEngine->intersects($geometry1, $geometry2));
    }

    public function providerIntersects() : array
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
     * @dataProvider providerTouches
     *
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $touches   Whether the geometries spatially touch.
     */
    public function testTouches(string $geometry1, string $geometry2, bool $touches) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'touches');

        self::assertSame($touches, $geometryEngine->touches($geometry1, $geometry2));
    }

    public function providerTouches() : array
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
     * @dataProvider providerCrosses
     *
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $crosses   Whether the geometries spatially cross.
     */
    public function testCrosses(string $geometry1, string $geometry2, bool $crosses) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'crosses');

        self::assertSame($crosses, $geometryEngine->crosses($geometry1, $geometry2));
    }

    public function providerCrosses() : array
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
     * @dataProvider providerWithin
     *
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $within    Whether the first geometry is within the second one.
     */
    public function testWithin(string $geometry1, string $geometry2, bool $within) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($within, $geometryEngine->within($geometry1, $geometry2));
    }

    public function providerWithin() : array
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
     * @dataProvider providerContains
     *
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $contains  Whether the first geometry contains the second one.
     */
    public function testContains(string $geometry1, string $geometry2, bool $contains) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($contains, $geometryEngine->contains($geometry1, $geometry2));
    }

    public function providerContains() : array
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
     * @dataProvider providerOverlaps
     *
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $overlaps  Whether the first geometry overlaps the second one.
     */
    public function testOverlaps(string $geometry1, string $geometry2, bool $overlaps) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($overlaps, $geometryEngine->overlaps($geometry1, $geometry2));
    }

    public function providerOverlaps() : array
    {
        return [
            ['POLYGON ((1 2, 2 4, 3 3, 2 1, 1 2))', 'POLYGON ((2 2, 2 3, 4 2, 3 1, 2 2))', true],
            ['POLYGON ((1 2, 2 4, 4 3, 2 1, 1 2))', 'POLYGON ((2 2, 2 3, 3 3, 2 2))', false],
        ];
    }

    /**
     * @dataProvider providerRelate
     *
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param string $matrix    The intersection matrix pattern.
     * @param bool   $relate    Whether the first geometry is spatially related to the second one.
     */
    public function testRelate(string $geometry1, string $geometry2, string $matrix, bool $relate) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        if ($this->isMySQL() || $this->isMariaDB('< 10.1.2')) {
            $this->expectException(GeometryEngineException::class);
        }

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($relate, $geometryEngine->relate($geometry1, $geometry2, $matrix));
    }

    public function providerRelate() : array
    {
        return [
            ['POLYGON ((60 160, 220 160, 220 20, 60 20, 60 160))', 'POLYGON ((60 160, 20 200, 260 200, 140 80, 60 160))', '212101212', true],
            ['POLYGON ((2 3, 8 3, 4 8, 2 3))', 'POLYGON ((-3 3, 3 3, 3 6, -3 6, -3 3))', 'T*F**F***', false],
            ['POINT (10.02 20.01)', 'POINT (10.02 20.01)', 'T*F**FFF*', true],
            ['POINT (10.02 20.01)', 'POINT (30.01 20.01)', 'T*F**FFF*', false],
        ];
    }

    /**
     * @dataProvider providerLocateAlong
     *
     * @param string $geometry The WKT of the base geometry.
     * @param float  $measure  The test measure.
     * @param string $result   The WKT of the result geometry.
     */
    public function testLocateAlong(string $geometry, float $measure, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        if ($this->isGEOS() || $this->isMySQL() || $this->isMariaDB()) {
            $this->expectException(GeometryEngineException::class);
        }

        self::assertSame($result, $geometryEngine->locateAlong(Geometry::fromText($geometry), $measure)->asText());
    }

    public function providerLocateAlong() : array
    {
        return [
            ['MULTILINESTRING M((1 2 3, 3 4 2, 9 4 3), (1 2 3, 5 4 5))', 3.0, 'MULTIPOINT M (1 2 3, 9 4 3, 1 2 3)'],
            ['MULTIPOINT M (1 2 3, 2 3 4, 3 4 5)', 5.0, 'MULTIPOINT M (3 4 5)'],
        ];
    }

    /**
     * @dataProvider providerLocateBetween
     *
     * @param string $geometry The WKT of the geometry to test.
     * @param float  $mStart   The start measure.
     * @param float  $mEnd     The end measure.
     * @param string $result   The WKT of the second geometry.
     */
    public function testLocateBetween(string $geometry, float $mStart, float $mEnd, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        if ($this->isGEOS() || $this->isMySQL() || $this->isMariaDB()) {
            $this->expectException(GeometryEngineException::class);
        }

        self::assertSame($result, $geometryEngine->locateBetween(Geometry::fromText($geometry), $mStart, $mEnd)->asText());
    }

    public function providerLocateBetween() : array
    {
        return [
            ['MULTIPOINT M(1 2 2)', 2.0, 5.0, 'MULTIPOINT M (1 2 2)'],
            ['MULTIPOINT M(1 2 2, 2 3 4, 3 4 5, 4 5 6, 5 6 7)', 4.0, 5.0, 'MULTIPOINT M (2 3 4, 3 4 5)'],
        ];
    }

    /**
     * @dataProvider providerDistance
     *
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param float  $distance  The distance between the geometries.
     */
    public function testDistance(string $geometry1, string $geometry2, float $distance) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertEqualsWithDelta($distance, $geometryEngine->distance($geometry1, $geometry2), 1e-14);
    }

    public function providerDistance() : array
    {
        return [
            ['POINT(2 1)', 'LINESTRING (3 0, 3 3)', 1.0],
            ['POLYGON ((1 1, 1 3, 4 4, 6 3, 5 1, 1 1))', 'POLYGON ((1 4, 3 5, 3 4, 1 4))', 0.316227766016838],
        ];
    }

    /**
     * @dataProvider providerBuffer
     *
     * @param string $geometry The WKT of the base geometry.
     * @param float  $distance The distance of the buffer.
     */
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

    public function providerBuffer() : array
    {
        return [
            ['POINT (1 2)', 3.0],
            ['LINESTRING (1 1, 3 3, 5 1)', 2.0],
            ['POLYGON ((0 0, 0 1, 1 1, 1 0, 0 0))', 4.0],
        ];
    }

    /**
     * @dataProvider providerConvexHull
     *
     * @param string $geometry The WKT of the base geometry.
     * @param string $result   The WKT of the result geometry.
     */
    public function testConvexHull(string $geometry, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        if ($this->isMySQL('< 5.7.6-m16') || $this->isMariaDB('< 10.1.2')) {
            $this->expectException(GeometryEngineException::class);
        }

        $geometry = Geometry::fromText($geometry);
        $result   = Geometry::fromText($result);

        $this->assertGeometryEquals($result, $geometryEngine->convexHull($geometry));
    }

    public function providerConvexHull() : array
    {
        return [
            ['LINESTRING (20 20, 30 30, 20 40, 30 50)', 'POLYGON ((20 20, 20 40, 30 50, 30 30, 20 20))'],
            ['POLYGON ((30 30, 25 35, 15 50, 35 80, 40 85, 80 90,70 75, 65 70, 55 50, 75 40, 60 30, 30 30))', 'POLYGON ((30 30, 25 35, 15 50, 35 80, 40 85, 80 90, 75 40, 60 30, 30 30))'],
            ['MULTIPOINT (20 20, 30 30, 20 40, 30 50)', 'POLYGON ((20 20, 20 40, 30 50, 30 30, 20 20))'],
        ];
    }

    /**
     * @dataProvider providerIntersection
     *
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param string $result    The WKT of the result geometry.
     */
    public function testIntersection(string $geometry1, string $geometry2, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);
        $result    = Geometry::fromText($result);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'intersection');

        $this->assertGeometryEquals($result, $geometryEngine->intersection($geometry1, $geometry2));
    }

    public function providerIntersection() : array
    {
        return [
            ['POINT (0 0)', 'LINESTRING (0 0, 0 2)', 'POINT (0 0)'],
            ['POLYGON ((1 1, 1 3, 4 4, 6 3, 5 1, 1 1))', 'POLYGON ((0 3, 6 5, 6 3, 0 3))', 'POLYGON ((1 3, 4 4, 6 3, 1 3))'],
        ];
    }

    /**
     * @dataProvider providerSymDifference
     *
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param string $result    The WKT of the result geometry.
     */
    public function testSymDifference(string $geometry1, string $geometry2, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);
        $result    = Geometry::fromText($result);

        $difference = $geometryEngine->symDifference($geometry1, $geometry2);

        $this->assertGeometryEquals($result, $difference);
    }

    public function providerSymDifference() : array
    {
        return [
            ['POLYGON ((1 1, 1 2, 2 2, 2 4, 4 4, 4 1, 1 1))', 'POLYGON ((3 0, 3 3, 5 3, 5 0, 3 0))', 'MULTIPOLYGON (((1 1, 1 2, 2 2, 2 4, 4 4, 4 3, 3 3, 3 1, 1 1)), ((3 1, 4 1, 4 3, 5 3, 5 0, 3 0, 3 1)))'],
        ];
    }

    /**
     * @dataProvider providerSnapToGrid
     *
     * @param string $geometry The WKT of the geometry to test.
     * @param float  $size     The grid size.
     * @param string $result   The WKT of the result geometry.
     */
    public function testSnapToGrid(string $geometry, float $size, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        if ($this->isGEOS() || $this->isMySQL() || $this->isMariaDB()) {
            $this->expectException(GeometryEngineException::class);
        }

        $geometry = Geometry::fromText($geometry);
        $result   = Geometry::fromText($result);

        $snapToGrid = $geometryEngine->snapToGrid($geometry, $size);

        $this->assertGeometryEquals($result, $snapToGrid);
    }

    public function providerSnapToGrid() : array
    {
        return [
            ['POINT (1.23 4.56)', 1.0, 'POINT (1 5)'],
            ['POINT (1.23 4.56)', 0.5, 'POINT (1.0 4.5)'],
            ['POINT (1.23 4.56)', 2, 'POINT (2 4)'],
            ['LINESTRING (1.1115678 2.123, 4.111111 3.2374897, 4.11112 3.23748667)', 0.001, 'LINESTRING (1.112 2.123, 4.111 3.237)'],
        ];
    }

    /**
     * @dataProvider providerSimplify
     *
     * @param string $geometry  The WKT of the geometry to test.
     * @param float  $tolerance The tolerance.
     * @param string $result    The WKT of the result geometry.
     */
    public function testSimplify(string$geometry, float $tolerance, string $result) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        if ($this->isMySQL('< 5.7.6-m16') || $this->isMariaDB('>= 10.0') || $this->isSpatiaLite('< 4.1.0')) {
            $this->expectException(GeometryEngineException::class);
        }

        $geometry = Geometry::fromText($geometry);
        $result   = Geometry::fromText($result);

        $this->assertGeometryEquals($result, $geometryEngine->simplify($geometry, $tolerance));
    }

    public function providerSimplify() : array
    {
        return [
            ['POLYGON ((4 0, 2 1, 1 2, 0 4, 0 6, 1 8, 2 9, 4 10, 6 10, 8 9, 9 8, 10 6, 10 4, 9 2, 8 1, 6 0, 4 0))', 1, 'POLYGON ((4 0, 1 2, 0 6, 2 9, 6 10, 9 8, 10 4, 8 1, 4 0))'],
            ['POLYGON ((4 0, 2 1, 1 2, 0 4, 0 6, 1 8, 2 9, 4 10, 6 10, 8 9, 9 8, 10 6, 10 4, 9 2, 8 1, 6 0, 4 0))', 2, 'POLYGON ((4 0, 0 6, 6 10, 10 4, 4 0))'],
        ];
    }

    /**
     * @dataProvider providerMaxDistance
     *
     * @param string $geometry1   The WKT of the first geometry.
     * @param string $geometry2   The WKT of the second geometry.
     * @param float  $maxDistance The expected value.
     */
    public function testMaxDistance(string $geometry1, string $geometry2, float $maxDistance) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        if ($this->isGEOS() || $this->isMySQL() || $this->isMariaDB() || $this->isSpatiaLite()) {
            $this->expectException(GeometryEngineException::class);
        }

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($maxDistance, $geometryEngine->maxDistance($geometry1, $geometry2));
    }

    public function providerMaxDistance() : array
    {
        return [
            ['POINT (0 0)', 'LINESTRING (2 0, 0 2)', 2.0],
            ['POINT (0 0)', 'LINESTRING (1 1, 0 4)', 4.0],
            ['LINESTRING (1 1, 3 1)', 'LINESTRING (4 1, 6 1)', 5.0],
        ];
    }

    /**
     * @dataProvider providerTransform
     */
    public function testTransform(string $originalWKT, int $originalSRID, int $targetSRID, string $expectedWKT) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        if ($this->isGEOS()) {
            $this->expectException(GeometryEngineException::class);
        } elseif (! $this->isPostGIS()) {
            self::markTestSkipped('This test currently runs on PostGIS only.');
        }

        $originalGeometry = Geometry::fromText($originalWKT, $originalSRID);
        $expectedGeometry = Geometry::fromText($expectedWKT, $targetSRID);

        $transformedGeometry = $geometryEngine->transform($originalGeometry, $targetSRID);

        $this->assertGeometryEqualsWithDelta($expectedGeometry, $transformedGeometry, 0.0000001);
    }

    public function providerTransform() : array
    {
        return [
            ['POINT (743238 2967416)', 2249, 4326, 'POINT (-71.1776848522251 42.3902896512902)'],
            ['POINT (743238 2967450)', 2249, 4326, 'POINT (-71.1776843766326 42.3903829478009)'],
            ['POINT (743265 2967450)', 2249, 4326, 'POINT (-71.1775844305465 42.3903826677917)'],
            ['POINT (743265.625 2967416)', 2249, 4326, 'POINT (-71.1775825927231 42.3902893647987)'],
        ];
    }

    private function getGeometryEngine(): GeometryEngine
    {
        if (! isset($GLOBALS['GEOMETRY_ENGINE'])) {
            self::markTestSkipped('This test requires a geometry engine to be set.');
        }

        return $GLOBALS['GEOMETRY_ENGINE'];
    }

    private function isMySQL(?string $operatorAndVersion = null) : bool
    {
        return $this->isMySQLorMariaDB(false, $operatorAndVersion);
    }

    private function isMariaDB(?string $operatorAndVersion = null) : bool
    {
        return $this->isMySQLorMariaDB(true, $operatorAndVersion);
    }

    private function isPostGIS() : bool
    {
        return $this->isPDODriver('pgsql');
    }

    /**
     * @param string|null $operatorAndVersion An optional version to satisfy.
     */
    private function isSpatiaLite(?string $operatorAndVersion = null) : bool
    {
        $engine = $this->getGeometryEngine();

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
    private function isGEOS(?string $operatorAndVersion = null) : bool
    {
        $engine = $this->getGeometryEngine();

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
    private function requireEngine(array $supportedEngines): void
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

    private function skipIfUnsupportedGeometry(Geometry $geometry) : void
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

    private function skipIfUnsupportedByEngine(Geometry $geometry1, Geometry $geometry2, string $methodName) : void
    {
        $this->skipIfUnsupportedGeometry($geometry1);
        $this->skipIfUnsupportedGeometry($geometry2);

        if ($this->isMySQL('< 5.7')) {
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
        $expectedWKT = $expected->asText();
        $actualWKT = $actual->asText();

        if ($expectedWKT === $actualWKT) {
            // Some engines do not consider empty geometries to be equal, so we test for WKT equality first.
            $this->addToAssertionCount(1);

            return;
        }

        self::assertSame($expected->geometryType(), $actual->geometryType());

        $geometryEngine = $this->getGeometryEngine();

        self::assertTrue($geometryEngine->equals($actual, $expected), 'Failed asserting that two geometries are spatially equal.'
            . "\n---Expected"
            . "\n+++Actual"
            . "\n@@ @@"
            . "\n-" . $expectedWKT
            . "\n+" . $actualWKT
        );
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
        $engine = $this->getGeometryEngine();

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
        $engine = $this->getGeometryEngine();

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
