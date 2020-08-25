<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\GeometryEngineException;
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
     *
     * @return void
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

    /**
     * @expectedException \Brick\Geo\Exception\UnexpectedGeometryException
     *
     * @return void
     */
    public function testFromTextOnWrongSubclassThrowsException() : void
    {
        Point::fromText('LINESTRING (1 2, 3 4)');
    }

    /**
     * @dataProvider providerTextBinary
     *
     * @param string $text               The WKT of the geometry under test.
     * @param string $bigEndianBinary    The big endian WKB of the geometry under test.
     * @param string $littleEndianBinary The little endian WKB of the geometry under test.
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return array
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
     *
     * @param string $geometry
     * @param int    $dimension
     *
     * @return void
     */
    public function testDimension(string $geometry, int $dimension) : void
    {
        $geometry = Geometry::fromText($geometry);
        self::assertSame($dimension, $geometry->dimension());
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testCoordinateDimension(string $geometry, int $coordinateDimension) : void
    {
        self::assertSame($coordinateDimension, Geometry::fromText($geometry)->coordinateDimension());
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testSpatialDimension(string $geometry, int $spatialDimension) : void
    {
        self::assertSame($spatialDimension, Geometry::fromText($geometry)->spatialDimension());
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testGeometryType(string $geometry, string $geometryType) : void
    {
        $geometry = Geometry::fromText($geometry);
        self::assertSame($geometryType, $geometry->geometryType());
    }

    /**
     * @return array
     */
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
     *
     * @param int $srid
     *
     * @return void
     */
    public function testSRID(int $srid) : void
    {
        self::assertSame($srid, Geometry::fromText('POINT EMPTY', $srid)->SRID());
    }

    /**
     * @return array
     */
    public function providerSRID() : array
    {
        return [
            [4326],
            ['4327'],
        ];
    }

    /**
     * @dataProvider providerEnvelope
     *
     * @param string $geometry The WKT of the geometry to test.
     * @param string $envelope The WKT of the expected envelope.
     *
     * @return void
     */
    public function testEnvelope(string $geometry, string $envelope) : void
    {
        $this->requiresGeometryEngine();

        $geometry = Geometry::fromText($geometry);
        $envelope = Geometry::fromText($envelope);

        $this->assertGeometryEquals($envelope, $geometry->envelope());
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testIsEmpty(string $geometry, bool $isEmpty) : void
    {
        self::assertSame($isEmpty, Geometry::fromText($geometry)->isEmpty());
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testIsValid(string $geometry, bool $isValid) : void
    {
        $this->requiresGeometryEngine();

        if ($this->isMySQL('< 5.7.6-m16') || $this->isMariaDB('>= 10.0')) {
            $this->expectException(GeometryEngineException::class);
        }

        $geometry = Geometry::fromText($geometry);

        $this->skipIfUnsupportedGeometry($geometry);

        self::assertSame($isValid, $geometry->isValid());
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testIsSimple(string $geometry, bool $isSimple) : void
    {
        $this->requiresGeometryEngine();

        $geometry = Geometry::fromText($geometry);
        $this->skipIfUnsupportedGeometry($geometry);
        self::assertSame($isSimple, $geometry->isSimple());
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testDimensionality(string $geometry, bool $is3D, bool $isMeasured) : void
    {
        self::assertSame($is3D, Geometry::fromText($geometry)->is3D());
        self::assertSame($isMeasured, Geometry::fromText($geometry)->isMeasured());
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testBoundary(string $geometry, string $boundary) : void
    {
        $this->requiresGeometryEngine();

        $geometry = Geometry::fromText($geometry);

        if ($this->isMySQL() || $this->isMariaDB()) {
            // MySQL and MariaDB do not support boundary.
            $this->expectException(GeometryEngineException::class);
        }

        if ($this->isSpatiaLite() && $geometry instanceof Point) {
            // SpatiaLite fails to return a result for a point's boundary.
            $this->expectException(GeometryEngineException::class);
        }

        self::assertSame($boundary, $geometry->boundary()->asText());
    }

    /**
     * @return array
     */
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
     * @dataProvider providerEquals
     *
     * @param string $geometry1 The WKT of the first geometry.
     * @param string $geometry2 The WKT of the second geometry.
     * @param bool   $equals    Whether the geometries are spatially equal.
     *
     * @return void
     */
    public function testEquals(string $geometry1, string $geometry2, bool $equals) : void
    {
        $this->requiresGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedGeometry($geometry1);
        $this->skipIfUnsupportedGeometry($geometry2);

        self::assertSame($equals, $geometry1->equals($geometry2));
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testDisjoint(string $geometry1, string $geometry2, bool $disjoint) : void
    {
        $this->requiresGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'disjoint');

        self::assertSame($disjoint, $geometry1->disjoint($geometry2));
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testIntersects(string $geometry1, string $geometry2, bool $intersects) : void
    {
        $this->requiresGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'intersects');

        self::assertSame($intersects, $geometry1->intersects($geometry2));
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testTouches(string $geometry1, string $geometry2, bool $touches) : void
    {
        $this->requiresGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'touches');

        self::assertSame($touches, $geometry1->touches($geometry2));
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testCrosses(string $geometry1, string $geometry2, bool $crosses) : void
    {
        $this->requiresGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'crosses');

        self::assertSame($crosses, $geometry1->crosses($geometry2));
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testWithin(string $geometry1, string $geometry2, bool $within) : void
    {
        $this->requiresGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($within, $geometry1->within($geometry2));
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testContains(string $geometry1, string $geometry2, bool $contains) : void
    {
        $this->requiresGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($contains, $geometry1->contains($geometry2));
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testOverlaps(string $geometry1, string $geometry2, bool $overlaps) : void
    {
        $this->requiresGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($overlaps, $geometry1->overlaps($geometry2));
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testRelate(string $geometry1, string $geometry2, string $matrix, bool $relate) : void
    {
        $this->requiresGeometryEngine();

        if ($this->isMySQL() || $this->isMariaDB()) {
            $this->expectException(GeometryEngineException::class);
        }

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($relate, $geometry1->relate($geometry2, $matrix));
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testLocateAlong(string $geometry, float $measure, string $result) : void
    {
        $this->requiresGeometryEngine();

        if ($this->isGEOS() || $this->isMySQL() || $this->isMariaDB()) {
            $this->expectException(GeometryEngineException::class);
        }

        self::assertSame($result, Geometry::fromText($geometry)->locateAlong($measure)->asText());
    }

    /**
     * @return array
     */
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
     * @param string $mStart   The start measure.
     * @param string $mEnd     The end measure.
     * @param string $result   The WKT of the second geometry.
     *
     * @return void
     */
    public function testLocateBetween(string $geometry, string $mStart, string $mEnd, string $result) : void
    {
        $this->requiresGeometryEngine();

        if ($this->isGEOS() || $this->isMySQL() || $this->isMariaDB()) {
            $this->expectException(GeometryEngineException::class);
        }

        self::assertSame($result, Geometry::fromText($geometry)->locateBetween($mStart, $mEnd)->asText());
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testDistance(string $geometry1, string $geometry2, float $distance) : void
    {
        $this->requiresGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($distance, $geometry1->distance($geometry2));
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testBuffer(string $geometry, float $distance) : void
    {
        $this->requiresGeometryEngine();

        $geometry = Geometry::fromText($geometry);
        $buffer = $geometry->buffer($distance);

        self::assertInstanceOf(Polygon::class, $buffer);
        self::assertTrue($buffer->contains($geometry));

        /** @var Polygon $buffer */
        $ring = $buffer->exteriorRing();

        for ($n = 1; $n <= $ring->numPoints(); $n++) {
            self::assertEquals($distance, $ring->pointN($n)->distance($geometry), '', 0.001);
        }
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testConvexHull(string $geometry, string $result) : void
    {
        $this->requiresGeometryEngine();

        if ($this->isMySQL('< 5.7.6-m16') || $this->isMariaDB('>= 10.0')) {
            $this->expectException(GeometryEngineException::class);
        }

        $geometry = Geometry::fromText($geometry);
        $result   = Geometry::fromText($result);

        $this->assertGeometryEquals($result, $geometry->convexHull());
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testIntersection(string $geometry1, string $geometry2, string $result) : void
    {
        $this->requiresGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);
        $result    = Geometry::fromText($result);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'intersection');

        $this->assertGeometryEquals($result, $geometry1->intersection($geometry2));
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testUnion(string $geometry1, string $geometry2, string $result) : void
    {
        $this->requiresGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);
        $result    = Geometry::fromText($result);

        $this->skipIfUnsupportedGeometry($geometry1);
        $this->skipIfUnsupportedGeometry($geometry2);

        $union = $geometry1->union($geometry2);

        if ($union->asText() === $result->asText()) {
            // GEOS does not consider POINT EMPTY to be equal to another POINT EMPTY;
            // a successful WKT comparison is a successful assertion for us here.
            $this->addToAssertionCount(1);

            return;
        }

        self::assertSame($result->geometryType(), $union->geometryType());
        self::assertTrue($union->equals($result));
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testDifference(string $geometry1, string $geometry2, string $result) : void
    {
        $this->requiresGeometryEngine();

        if ($this->isMySQL('< 5.7')) {
            self::markTestSkipped('MySQL 5.6 difference() implementation is very buggy and should not be used.');
        }

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);
        $result    = Geometry::fromText($result);

        $difference = $geometry1->difference($geometry2);
        $this->assertGeometryEquals($result, $difference);
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testSymDifference(string $geometry1, string $geometry2, string $result) : void
    {
        $this->requiresGeometryEngine();

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);
        $result    = Geometry::fromText($result);

        $difference = $geometry1->symDifference($geometry2);

        $this->assertGeometryEquals($result, $difference);
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testSnapToGrid(string $geometry, float $size, string $result) : void
    {
        $this->requiresGeometryEngine();

        if ($this->isGEOS() || $this->isMySQL() || $this->isMariaDB()) {
            $this->expectException(GeometryEngineException::class);
        }

        $geometry = Geometry::fromText($geometry);
        $result   = Geometry::fromText($result);

        $snapToGrid = $geometry->snapToGrid($size);

        $this->assertGeometryEquals($result, $snapToGrid);
    }

    /**
     * @return array
     */
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
     *
     * @return void
     */
    public function testSimplify(string$geometry, float $tolerance, string $result) : void
    {
        $this->requiresGeometryEngine();

        if ($this->isMySQL('< 5.7.6-m16') || $this->isMariaDB('>= 10.0') || $this->isSpatiaLite('< 4.1.0')) {
            $this->expectException(GeometryEngineException::class);
        }

        $geometry = Geometry::fromText($geometry);
        $result   = Geometry::fromText($result);

        $this->assertGeometryEquals($result, $geometry->simplify($tolerance));
    }

    /**
     * @return array
     */
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
     * @param string $geometry1    The WKT of the first geometry.
     * @param string $geometry2    The WKT of the second geometry.
     * @param float  $maxDistance  The expected value.
     *
     * @return void
     */
    public function testMaxDistance(string $geometry1, string $geometry2, float $maxDistance) : void
    {
        $this->requiresGeometryEngine();

        if ($this->isGEOS() || $this->isMySQL() || $this->isMariaDB() || $this->isSpatiaLite()) {
            $this->expectException(GeometryEngineException::class);
        }

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        self::assertSame($maxDistance, $geometry1->maxDistance($geometry2));
    }

    /**
     * @return array
     */
    public function providerMaxDistance() : array
    {
        return [
            ['POINT (0 0)', 'LINESTRING (2 0, 0 2)', 2.0],
            ['POINT (0 0)', 'LINESTRING (1 1, 0 4)', 4.0],
            ['LINESTRING (1 1, 3 1)', 'LINESTRING (4 1, 6 1)', 5.0],
        ];
    }

    /**
     * @dataProvider providerToArray
     *
     * @param string $geometry The WKT of the geometry to test.
     * @param array  $array    The expected result array.
     *
     * @return void
     */
    public function testToArray(string $geometry, array $array) : void
    {
        $this->castToFloat($array);
        self::assertSame($array, Geometry::fromText($geometry)->toArray());
    }

    /**
     * @return array
     */
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
