<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Exception\UnexpectedGeometryException;
use Brick\Geo\IO\WKBTools;
use Brick\Geo\Geometry;
use Brick\Geo\Point;
use Brick\Geo\Polygon;

/**
 * Unit tests for Geometry.
 */
class GeometryTest extends AbstractTestCase
{
    /**
     * @dataProvider providerTextBinary
     *
     * @param string $text The WKT of the geometry to test.
     */
    public function testFromAsText(string $text) : void
    {
        $geometry = Geometry::fromText($text);

        self::assertSame($text, $geometry->asText());
        self::assertSame(0, $geometry->SRID());

        $geometry = Geometry::fromText($text, 4326);

        self::assertSame($text, $geometry->asText());
        self::assertSame(4326, $geometry->SRID());
    }

    public function testFromTextOnWrongSubclassThrowsException() : void
    {
        $this->expectException(UnexpectedGeometryException::class);
        Point::fromText('LINESTRING (1 2, 3 4)');
    }

    /**
     * @dataProvider providerTextBinary
     *
     * @param string $text               The WKT of the geometry under test.
     * @param string $bigEndianBinary    The big endian WKB of the geometry under test.
     * @param string $littleEndianBinary The little endian WKB of the geometry under test.
     */
    public function testFromBinary(string $text, string $bigEndianBinary, string $littleEndianBinary) : void
    {
        foreach ([$bigEndianBinary, $littleEndianBinary] as $binary) {
            $geometry = Geometry::fromBinary(hex2bin($binary));

            self::assertSame($text, $geometry->asText());
            self::assertSame(0, $geometry->SRID());

            $geometry = Geometry::fromBinary(hex2bin($binary), 4326);

            self::assertSame($text, $geometry->asText());
            self::assertSame(4326, $geometry->SRID());
        }
    }

    /**
     * @dataProvider providerTextBinary
     *
     * @param string $text               The WKT of the geometry under test.
     * @param string $bigEndianBinary    The big endian WKB of the geometry under test.
     * @param string $littleEndianBinary The little endian WKB of the geometry under test.
     */
    public function testAsBinary(string $text, string $bigEndianBinary, string $littleEndianBinary) : void
    {
        $machineByteOrder = WKBTools::getMachineByteOrder();

        if ($machineByteOrder === WKBTools::BIG_ENDIAN) {
            $binary = $bigEndianBinary;
        } else {
            $binary = $littleEndianBinary;
        }

        self::assertSame($binary, bin2hex(Geometry::fromText($text)->asBinary()));
    }

    /**
     * This is a very succinct series of tests for text/binary import/export methods.
     * Exhaustive tests for WKT and WKB are in the IO directory.
     */
    public function providerTextBinary() : array
    {
        return [
            ['POINT (1 2)', '00000000013ff00000000000004000000000000000', '0101000000000000000000f03f0000000000000040'],
            ['LINESTRING Z EMPTY', '00000003ea00000000', '01ea03000000000000'],
            ['MULTIPOLYGON M EMPTY', '00000007d600000000', '01d607000000000000'],
            ['POLYHEDRALSURFACE ZM EMPTY', '0000000bc700000000', '01c70b000000000000'],
        ];
    }

    /**
     * @dataProvider providerDimension
     */
    public function testDimension(string $geometry, int $dimension) : void
    {
        $geometry = Geometry::fromText($geometry);
        self::assertSame($dimension, $geometry->dimension());
    }

    public function providerDimension() : array
    {
        return [
            ['POINT EMPTY', 0],
            ['POINT Z EMPTY', 0],
            ['POINT M EMPTY', 0],
            ['POINT ZM EMPTY', 0],
            ['POINT (1 2)', 0],
            ['POINT Z (1 2 3)', 0],
            ['POINT M (1 2 3)', 0],
            ['POINT ZM (1 2 3 4)', 0],
            ['LINESTRING EMPTY', 1],
            ['LINESTRING Z EMPTY', 1],
            ['LINESTRING M EMPTY', 1],
            ['LINESTRING ZM EMPTY', 1],
            ['LINESTRING (1 2, 3 4)', 1],
            ['LINESTRING Z (1 2 3, 4 5 6)', 1],
            ['LINESTRING M (2 3 4, 5 6 7)', 1],
            ['LINESTRING ZM (1 2 3 4, 5 6 7 8)', 1],
            ['POLYGON EMPTY', 2],
            ['POLYGON Z EMPTY', 2],
            ['POLYGON M EMPTY', 2],
            ['POLYGON ZM EMPTY', 2],
            ['POLYGON ((0 0, 0 1, 1 1, 1 0, 0 0))', 2],
            ['POLYGON Z ((0 0 0, 0 1 0, 1 1 0, 1 0 0, 0 0 0))', 2],
            ['POLYGON M ((0 0 1, 0 1 2, 1 1 3, 1 0 4, 0 0 1))', 2],
            ['POLYGON ZM ((0 0 0 1, 0 1 0 2, 1 1 0 3, 1 0 0 4, 0 0 0 1))', 2],
            ['MULTIPOINT EMPTY', 0],
            ['MULTIPOINT Z EMPTY', 0],
            ['MULTIPOINT M EMPTY', 0],
            ['MULTIPOINT ZM EMPTY', 0],
            ['MULTILINESTRING EMPTY', 1],
            ['MULTILINESTRING Z EMPTY', 1],
            ['MULTILINESTRING M EMPTY', 1],
            ['MULTILINESTRING ZM EMPTY', 1],
            ['MULTIPOLYGON EMPTY', 2],
            ['MULTIPOLYGON Z EMPTY', 2],
            ['MULTIPOLYGON M EMPTY', 2],
            ['MULTIPOLYGON ZM EMPTY', 2],
            ['GEOMETRYCOLLECTION EMPTY', 0],
            ['GEOMETRYCOLLECTION Z EMPTY', 0],
            ['GEOMETRYCOLLECTION M EMPTY', 0],
            ['GEOMETRYCOLLECTION ZM EMPTY', 0],
            ['GEOMETRYCOLLECTION (POINT (1 1))', 0],
            ['GEOMETRYCOLLECTION (POINT (1 1), MULTILINESTRING EMPTY)', 1],
            ['GEOMETRYCOLLECTION (POLYGON EMPTY, LINESTRING (1 1, 2 2), POINT (3 3))', 2],
            ['GEOMETRYCOLLECTION Z (LINESTRING Z (1 2 3, 4 5 6))', 1],
            ['TRIANGLE ((0 0, 0 1, 1 0, 0 0))', 2],
        ];
    }

    /**
     * @dataProvider providerCoordinateDimension
     *
     * @param string $geometry            The WKT of the geometry to test.
     * @param int    $coordinateDimension The expected coordinate dimension.
     */
    public function testCoordinateDimension(string $geometry, int $coordinateDimension) : void
    {
        self::assertSame($coordinateDimension, Geometry::fromText($geometry)->coordinateDimension());
    }

    public function providerCoordinateDimension() : array
    {
        return [
            ['POINT (1 2)', 2],
            ['POINT Z (1 2 3)', 3],
            ['POINT M (1 2 3)', 3],
            ['POINT ZM (1 2 3 4)', 4],
            ['LINESTRING (1 2, 3 4)', 2],
            ['LINESTRING Z (1 2 3, 4 5 6)', 3],
            ['LINESTRING M (1 2 3, 4 5 6)', 3],
            ['LINESTRING ZM (1 2 3 4, 5 6 7 8)', 4],
        ];
    }

    /**
     * @dataProvider providerSpatialDimension
     *
     * @param string $geometry         The WKT of the geometry to test.
     * @param int    $spatialDimension The expected spatial dimension.
     */
    public function testSpatialDimension(string $geometry, int $spatialDimension) : void
    {
        self::assertSame($spatialDimension, Geometry::fromText($geometry)->spatialDimension());
    }

    public function providerSpatialDimension() : array
    {
        return [
            ['POINT (1 2)', 2],
            ['POINT Z (1 2 3)', 3],
            ['POINT M (1 2 3)', 2],
            ['POINT ZM (1 2 3 4)', 3],
            ['LINESTRING (1 2, 3 4)', 2],
            ['LINESTRING Z (1 2 3, 4 5 6)', 3],
            ['LINESTRING M (1 2 3, 4 5 6)', 2],
            ['LINESTRING ZM (1 2 3 4, 5 6 7 8)', 3],
        ];
    }

    /**
     * @dataProvider providerGeometryType
     *
     * @param string $geometry     The WKT of the geometry to test.
     * @param string $geometryType The expected geometry type.
     */
    public function testGeometryType(string $geometry, string $geometryType) : void
    {
        $geometry = Geometry::fromText($geometry);
        self::assertSame($geometryType, $geometry->geometryType());
    }

    public function providerGeometryType() : array
    {
        return [
            ['POINT EMPTY', 'Point'],
            ['POINT Z EMPTY', 'Point'],
            ['POINT M EMPTY', 'Point'],
            ['POINT ZM EMPTY', 'Point'],
            ['POINT (1 2)', 'Point'],
            ['POINT Z (1 2 3)', 'Point'],
            ['POINT M (1 2 3)', 'Point'],
            ['POINT ZM (1 2 3 4)' , 'Point'],
            ['LINESTRING EMPTY', 'LineString'],
            ['LINESTRING Z EMPTY', 'LineString'],
            ['LINESTRING M EMPTY', 'LineString'],
            ['LINESTRING ZM EMPTY', 'LineString'],
            ['LINESTRING (1 2, 3 4)', 'LineString'],
            ['LINESTRING Z (1 2 3, 4 5 6)', 'LineString'],
            ['LINESTRING M (1 2 3, 4 5 6)', 'LineString'],
            ['LINESTRING ZM (1 2 3 4, 5 6 7 8)', 'LineString'],
            ['POLYGON EMPTY', 'Polygon'],
            ['POLYGON Z EMPTY', 'Polygon'],
            ['POLYGON M EMPTY', 'Polygon'],
            ['POLYGON ZM EMPTY', 'Polygon'],
            ['POLYGON ((0 0, 0 1, 1 1, 1 0, 0 0))', 'Polygon'],
            ['POLYGON Z ((0 0 0, 0 1 0, 1 1 0, 1 0 0, 0 0 0))', 'Polygon'],
            ['POLYGON M ((0 0 0, 0 1 0, 1 1 0, 1 0 0, 0 0 0))', 'Polygon'],
            ['POLYGON ZM ((0 0 0 0, 0 1 0 0, 1 1 0 0, 1 0 0 0, 0 0 0 0))', 'Polygon'],
            ['MULTIPOINT EMPTY', 'MultiPoint'],
            ['MULTIPOINT Z EMPTY', 'MultiPoint'],
            ['MULTIPOINT M EMPTY', 'MultiPoint'],
            ['MULTIPOINT ZM EMPTY', 'MultiPoint'],
            ['MULTILINESTRING EMPTY', 'MultiLineString'],
            ['MULTILINESTRING Z EMPTY', 'MultiLineString'],
            ['MULTILINESTRING M EMPTY', 'MultiLineString'],
            ['MULTILINESTRING ZM EMPTY', 'MultiLineString'],
            ['MULTIPOLYGON EMPTY', 'MultiPolygon'],
            ['MULTIPOLYGON Z EMPTY', 'MultiPolygon'],
            ['MULTIPOLYGON M EMPTY', 'MultiPolygon'],
            ['MULTIPOLYGON ZM EMPTY', 'MultiPolygon'],
            ['GEOMETRYCOLLECTION EMPTY', 'GeometryCollection'],
            ['GEOMETRYCOLLECTION Z EMPTY', 'GeometryCollection'],
            ['GEOMETRYCOLLECTION M EMPTY', 'GeometryCollection'],
            ['GEOMETRYCOLLECTION ZM EMPTY', 'GeometryCollection'],
            ['TRIANGLE EMPTY', 'Triangle'],
            ['TRIANGLE Z EMPTY', 'Triangle'],
            ['TRIANGLE M EMPTY', 'Triangle'],
            ['TRIANGLE ZM EMPTY', 'Triangle'],
            ['POLYHEDRALSURFACE EMPTY', 'PolyhedralSurface'],
            ['POLYHEDRALSURFACE Z EMPTY', 'PolyhedralSurface'],
            ['POLYHEDRALSURFACE M EMPTY', 'PolyhedralSurface'],
            ['POLYHEDRALSURFACE ZM EMPTY', 'PolyhedralSurface'],
            ['TIN EMPTY', 'TIN'],
            ['TIN Z EMPTY', 'TIN'],
            ['TIN M EMPTY', 'TIN'],
            ['TIN ZM EMPTY', 'TIN'],
        ];
    }

    /**
     * @dataProvider providerSRID
     */
    public function testSRID(int $srid) : void
    {
        self::assertSame($srid, Geometry::fromText('POINT EMPTY', $srid)->SRID());
    }

    public function providerSRID() : array
    {
        return [
            [4326],
            [4327],
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
     * @dataProvider providerIsEmpty
     *
     * @param string $geometry The WKT of the geometry to test.
     * @param bool   $isEmpty  Whether the geometry is empty.
     */
    public function testIsEmpty(string $geometry, bool $isEmpty) : void
    {
        self::assertSame($isEmpty, Geometry::fromText($geometry)->isEmpty());
    }

    public function providerIsEmpty() : array
    {
        return [
            ['POINT EMPTY', true],
            ['POINT (1 2)', false],
            ['LINESTRING EMPTY', true],
            ['LINESTRING (0 0, 1 1)', false],
            ['GEOMETRYCOLLECTION EMPTY', true],
            ['GEOMETRYCOLLECTION (POINT (1 2))', false],
            ['GEOMETRYCOLLECTION (POINT EMPTY, LINESTRING EMPTY)', true],
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
     * @dataProvider providerDimensionality
     *
     * @param string $geometry   The geometry to test.
     * @param bool   $is3D       Whether the geometry has a Z coordinate.
     * @param bool   $isMeasured Whether the geometry has a M coordinate.
     */
    public function testDimensionality(string $geometry, bool $is3D, bool $isMeasured) : void
    {
        self::assertSame($is3D, Geometry::fromText($geometry)->is3D());
        self::assertSame($isMeasured, Geometry::fromText($geometry)->isMeasured());
    }

    public function providerDimensionality() : array
    {
        return [
            ['POINT EMPTY', false, false],
            ['POINT Z EMPTY', true, false],
            ['POINT M EMPTY', false, true],
            ['POINT ZM EMPTY', true, true],
            ['LINESTRING (1 2, 3 4)', false, false],
            ['LINESTRING Z (1 2 3, 4 5 6)', true, false],
            ['LINESTRING M (1 2 3, 4 5 6)', false, true],
            ['LINESTRING ZM (1 2 3 4, 5 6 7 8)', true, true],
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
     * @dataProvider providerCentroid
     *
     * @param string   $geometry         The WKT of the geometry to calculate centroid for.
     * @param float    $centroidX        Expected `x` coordinate of the geometry centroid.
     * @param float    $centroidY        Expected `y` coordinate of the geometry centroid.
     * @param string[] $supportedEngines The engines that support this test.
     */
    public function testCentroid(string $geometry, float $centroidX, float $centroidY, array $supportedEngines) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $this->requireEngine($supportedEngines);

        $geometry = Geometry::fromText($geometry);

        $centroid = $geometryEngine->centroid($geometry);

        $this->assertEqualsWithDelta($centroidX, $centroid->x(), 0.001);
        $this->assertEqualsWithDelta($centroidY, $centroid->y(), 0.001);
    }

    public function providerCentroid() : array
    {
        return [
            ['POINT (42 42)', 42.0, 42.0, ['MySQL', 'SpatiaLite', 'PostGIS', 'GEOS']],
            ['MULTIPOINT (0 0, 1 1)', 0.5, 0.5, ['MySQL', 'SpatiaLite', 'PostGIS', 'GEOS']],
            ['CIRCULARSTRING (1 1, 2 0, -1 1)', 0.0, -1.373, ['PostGIS']],
            ['POLYGON ((0 1, 1 0, 0 -1, -1 0, 0 1))', 0.0, 0.0, ['MySQL', 'MariaDB', 'SpatiaLite', 'PostGIS', 'GEOS']],
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

    /**
     * @dataProvider providerWithSRID
     */
    public function testWithSRID(string $wkt): void
    {
        $geometry = Geometry::fromText($wkt)->withSRID(4326);

        $this->assertSRID(4326, $geometry);
        self::assertSame($wkt, $geometry->asText());
    }

    private function assertSRID(int $expectedSRID, Geometry $geometry): void
    {
        self::assertSame($expectedSRID, $geometry->SRID());

        foreach ($geometry as $value) {
            if ($value instanceof Geometry) {
                $this->assertSRID($expectedSRID, $value);
            }
        }
    }

    public function providerWithSRID(): array
    {
        return [
            ['POINT (1 2)'],
            ['POINT Z (1 2 3)'],
            ['POINT M (1 2 3)'],
            ['POINT ZM (1 2 3 4)'],
            ['LINESTRING (1 2, 3 4)'],
            ['LINESTRING Z (1 2 3, 4 5 6)'],
            ['LINESTRING M (1 2 3, 4 5 6)'],
            ['LINESTRING ZM (1 2 3 4, 5 6 7 8)'],
            ['POLYGON ((1 2, 3 4, 5 6, 1 2))'],
            ['POLYGON Z ((1 2 3, 4 5 6, 7 8 9, 1 2 3))'],
            ['POLYGON M ((1 2 3, 4 5 6, 7 8 9, 1 2 3))'],
            ['POLYGON ZM ((1 2 3 4, 5 6 7 8, 9 10 11 12, 1 2 3 4))'],
            ['MULTIPOINT (1 2, 3 4)'],
            ['MULTIPOINT Z (1 2 3, 4 5 6)'],
            ['MULTIPOINT M (1 2 3, 4 5 6)'],
            ['MULTIPOINT ZM (1 2 3 4, 5 6 7 8)'],
            ['MULTILINESTRING ((1 2, 3 4), (5 6, 7 8))'],
            ['MULTILINESTRING Z ((1 2 3, 4 5 6), (7 8 9, 10 11 12))'],
            ['MULTILINESTRING M ((1 2 3, 4 5 6), (7 8 9, 10 11 12))'],
            ['MULTILINESTRING ZM ((1 2 3 4, 5 6 7 8), (9 10 11 12, 13 14 15 16))'],
            ['MULTIPOLYGON (((1 2, 3 4, 5 6, 1 2)), ((7 8, 9 10, 11 12, 7 8)))'],
            ['MULTIPOLYGON Z (((1 2 3, 4 5 6, 7 8 9, 1 2 3)), ((10 11 12, 13 14 15, 16 17 18, 10 11 12)))'],
            ['MULTIPOLYGON M (((1 2 3, 4 5 6, 7 8 9, 1 2 3)), ((10 11 12, 13 14 15, 16 17 18, 10 11 12)))'],
            ['MULTIPOLYGON ZM (((1 2 3 4, 5 6 7 8, 9 10 11 12, 1 2 3 4)), ((13 14 15 16, 17 18 19 20, 21 22 23 24, 13 14 15 16)))'],
            ['GEOMETRYCOLLECTION (POINT (1 2), LINESTRING (3 4, 5 6))'],
            ['GEOMETRYCOLLECTION Z (POINT Z (1 2 3), LINESTRING Z (4 5 6, 7 8 9))'],
            ['GEOMETRYCOLLECTION M (POINT M (1 2 3), LINESTRING M (4 5 6, 7 8 9))'],
            ['GEOMETRYCOLLECTION ZM (POINT ZM (1 2 3 4), LINESTRING ZM (5 6 7 8, 9 10 11 12))'],
        ];
    }

    /**
     * @dataProvider providerToArray
     *
     * @param string $geometry The WKT of the geometry to test.
     * @param array  $array    The expected result array.
     */
    public function testToArray(string $geometry, array $array) : void
    {
        $this->castToFloat($array);
        self::assertSame($array, Geometry::fromText($geometry)->toArray());
    }

    public function providerToArray() : array
    {
        return [
            ['POINT EMPTY', []],
            ['POINT Z EMPTY', []],
            ['POINT M EMPTY', []],
            ['POINT ZM EMPTY', []],
            ['POINT (1 2)', [1, 2]],
            ['POINT Z (1 2 3)', [1, 2, 3]],
            ['POINT M (2 3 4)', [2, 3, 4]],
            ['POINT ZM (1 2 3 4)', [1, 2, 3, 4]],

            ['LINESTRING EMPTY', []],
            ['LINESTRING Z EMPTY', []],
            ['LINESTRING M EMPTY', []],
            ['LINESTRING ZM EMPTY', []],
            ['LINESTRING (1 2, 3 4, 5 6, 7 8)', [[1, 2], [3, 4], [5, 6], [7, 8]]],
            ['LINESTRING Z (1 2 3, 4 5 6, 7 8 9)', [[1, 2, 3], [4, 5, 6], [7, 8, 9]]],
            ['LINESTRING M (1 2 3, 4 5 6, 7 8 9)', [[1, 2, 3], [4, 5 , 6], [7, 8, 9]]],
            ['LINESTRING ZM (1 2 3 4, 5 6 7 8)', [[1, 2, 3, 4], [5, 6, 7, 8]]],
        ];
    }
}
