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
     */
    public function testFromAsText($text)
    {
        $geometry = Geometry::fromText($text);

        $this->assertSame($text, $geometry->asText());
        $this->assertSame(0, $geometry->SRID());

        $geometry = Geometry::fromText($text, 4326);

        $this->assertSame($text, $geometry->asText());
        $this->assertSame(4326, $geometry->SRID());
    }

    /**
     * @expectedException \Brick\Geo\Exception\GeometryException
     */
    public static function testFromTextOnWrongSubclassThrowsException()
    {
        Point::fromText('LINESTRING (1 2, 3 4)');
    }

    /**
     * @dataProvider providerTextBinary
     *
     * @param string $text               The WKT of the geometry under test.
     * @param string $bigEndianBinary    The big endian WKB of the geometry under test.
     * @param string $littleEndianBinary The little endian WKB of the geometry under test.
     */
    public function testFromBinary($text, $bigEndianBinary, $littleEndianBinary)
    {
        foreach ([$bigEndianBinary, $littleEndianBinary] as $binary) {
            $geometry = Geometry::fromBinary(hex2bin($binary));

            $this->assertSame($text, $geometry->asText());
            $this->assertSame(0, $geometry->SRID());

            $geometry = Geometry::fromBinary(hex2bin($binary), 4326);

            $this->assertSame($text, $geometry->asText());
            $this->assertSame(4326, $geometry->SRID());
        }
    }

    /**
     * @dataProvider providerTextBinary
     *
     * @param string $text               The WKT of the geometry under test.
     * @param string $bigEndianBinary    The big endian WKB of the geometry under test.
     * @param string $littleEndianBinary The little endian WKB of the geometry under test.
     */
    public function testAsBinary($text, $bigEndianBinary, $littleEndianBinary)
    {
        $machineByteOrder = WKBTools::getMachineByteOrder();

        if ($machineByteOrder === WKBTools::BIG_ENDIAN) {
            $binary = $bigEndianBinary;
        } else {
            $binary = $littleEndianBinary;
        }

        $this->assertSame($binary, bin2hex(Geometry::fromText($text)->asBinary()));
    }

    /**
     * This is a very succinct series of tests for text/binary import/export methods.
     * Exhaustive tests for WKT and WKB are in the IO directory.
     *
     * @return array
     */
    public function providerTextBinary()
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
     * @param string  $geometry
     * @param integer $dimension
     */
    public function testDimension($geometry, $dimension)
    {
        $geometry = Geometry::fromText($geometry);
        $this->assertSame($dimension, $geometry->dimension());
    }

    /**
     * @return array
     */
    public function providerDimension()
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
     * @param string  $geometry            The WKT of the geometry to test.
     * @param integer $coordinateDimension The expected coordinate dimension.
     */
    public function testCoordinateDimension($geometry, $coordinateDimension)
    {
        $this->assertSame($coordinateDimension, Geometry::fromText($geometry)->coordinateDimension());
    }

    /**
     * @return array
     */
    public function providerCoordinateDimension()
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
     * @param string  $geometry         The WKT of the geometry to test.
     * @param integer $spatialDimension The expected spatial dimension.
     */
    public function testSpatialDimension($geometry, $spatialDimension)
    {
        $this->assertSame($spatialDimension, Geometry::fromText($geometry)->spatialDimension());
    }

    /**
     * @return array
     */
    public function providerSpatialDimension()
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
    public function testGeometryType($geometry, $geometryType)
    {
        $geometry = Geometry::fromText($geometry);
        $this->assertSame($geometryType, $geometry->geometryType());
    }

    /**
     * @return array
     */
    public function providerGeometryType()
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
     * @param integer $srid
     */
    public function testSRID($srid)
    {
        $this->assertSame((int) $srid, Geometry::fromText('POINT EMPTY', $srid)->SRID());
    }

    /**
     * @return array
     */
    public function providerSRID()
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
     */
    public function envelope($geometry, $envelope)
    {
        $this->assertSame($envelope, Geometry::fromText($geometry)->envelope()->asText());
    }

    /**
     * @return array
     */
    public function providerEnvelope()
    {
        return [
            ['POINT (1 3)', 'POINT (1 3)'],
            ['LINESTRING (0 0, 1 3)', 'POLYGON ((0 0, 0 3, 1 3, 1 0, 0 0))'],
        ];
    }

    /**
     * @dataProvider providerIsEmpty
     *
     * @param string  $geometry The WKT of the geometry to test.
     * @param boolean $isEmpty  Whether the geometry is empty.
     */
    public function testIsEmpty($geometry, $isEmpty)
    {
        $this->assertSame($isEmpty, Geometry::fromText($geometry)->isEmpty());
    }

    /**
     * @return array
     */
    public function providerIsEmpty()
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
     * @param string  $geometry The WKT of the geometry to test.
     * @param boolean $isValid  Whether the geometry is valid.
     */
    public function testIsValid($geometry, $isValid)
    {
        if ($this->isMySQL('< 5.7.6-m16') || $this->isMariaDB('>= 10.0')) {
            $this->setExpectedException(GeometryEngineException::class);
        }

        $geometry = Geometry::fromText($geometry);

        $this->skipIfUnsupportedGeometry($geometry);

        $this->assertSame($isValid, $geometry->isValid());
    }

    /**
     * @return array
     */
    public function providerIsValid()
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
     * @param string  $geometry The WKT of the geometry to test.
     * @param boolean $isSimple Whether the geometry is simple.
     */
    public function testIsSimple($geometry, $isSimple)
    {
        $this->assertSame($isSimple, Geometry::fromText($geometry)->isSimple());
    }

    /**
     * @return array
     */
    public function providerIsSimple()
    {
        return [
            ['POINT (1 2)', true],
            ['LINESTRING (1 2, 3 4)', true],
            ['LINESTRING (0 0, 0 1, 1 1, 1 0)', true],
            ['LINESTRING (1 0, 1 2, 2 1, 0 1)', false]
        ];
    }

    /**
     * @dataProvider providerDimensionality
     *
     * @param string  $geometry   The geometry to test.
     * @param boolean $is3D       Whether the geometry has a Z coordinate.
     * @param boolean $isMeasured Whether the geometry has a M coordinate.
     */
    public function testDimensionality($geometry, $is3D, $isMeasured)
    {
        $this->assertSame($is3D, Geometry::fromText($geometry)->is3D());
        $this->assertSame($isMeasured, Geometry::fromText($geometry)->isMeasured());
    }

    /**
     * @return array
     */
    public function providerDimensionality()
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
    public function testBoundary($geometry, $boundary)
    {
        if ($this->isMySQL() || $this->isMariaDB()) {
            $this->setExpectedException(GeometryEngineException::class);
        }

        $this->assertSame($boundary, Geometry::fromText($geometry)->boundary()->asText());
    }

    /**
     * @return array
     */
    public function providerBoundary()
    {
        return [
            ['LINESTRING (1 1, 0 0, -1 1)', 'MULTIPOINT (1 1, -1 1)'],
            ['POLYGON ((1 1, 0 0, -1 1, 1 1))', 'LINESTRING (1 1, 0 0, -1 1, 1 1)'],
        ];
    }

    /**
     * @dataProvider providerEquals
     *
     * @param string  $geometry1 The WKT of the first geometry.
     * @param string  $geometry2 The WKT of the second geometry.
     * @param boolean $equals    Whether the geometries are spatially equal.
     */
    public function testEquals($geometry1, $geometry2, $equals)
    {
        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedGeometry($geometry1);
        $this->skipIfUnsupportedGeometry($geometry2);

        $this->assertSame($equals, $geometry1->equals($geometry2));
    }

    /**
     * @return array
     */
    public function providerEquals()
    {
        return [
            ['POINT (1 2)', 'POINT (1 2)', true],
            ['POINT (1 2)', 'POINT (1 3)', false],
            ['LINESTRING EMPTY', 'LINESTRING (1 2, 3 4)', false],
            ['POINT (1 2)', 'MULTIPOINT (1 2)', true],
            ['POLYGON ((1 2, 1 3, 2 2, 1 2))', 'POLYGON ((1 3, 2 2, 1 2, 1 3))', true]
        ];
    }

    /**
     * @dataProvider providerDisjoint
     *
     * @param string  $geometry1 The WKT of the first geometry.
     * @param string  $geometry2 The WKT of the second geometry.
     * @param boolean $disjoint  Whether the geometries are spatially disjoint.
     */
    public function testDisjoint($geometry1, $geometry2, $disjoint)
    {
        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'disjoint');

        $this->assertSame($disjoint, $geometry1->disjoint($geometry2));
    }

    /**
     * @return array
     */
    public function providerDisjoint()
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
     * @param string  $geometry1  The WKT of the first geometry.
     * @param string  $geometry2  The WKT of the second geometry.
     * @param boolean $intersects Whether the geometries spatially intersect.
     */
    public function testIntersects($geometry1, $geometry2, $intersects)
    {
        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'intersects');

        $this->assertSame($intersects, $geometry1->intersects($geometry2));
    }

    /**
     * @return array
     */
    public function providerIntersects()
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
     * @param string  $geometry1 The WKT of the first geometry.
     * @param string  $geometry2 The WKT of the second geometry.
     * @param boolean $touches   Whether the geometries spatially touch.
     */
    public function testTouches($geometry1, $geometry2, $touches)
    {
        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'touches');

        $this->assertSame($touches, $geometry1->touches($geometry2));
    }

    /**
     * @return array
     */
    public function providerTouches()
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
     * @param string  $geometry1 The WKT of the first geometry.
     * @param string  $geometry2 The WKT of the second geometry.
     * @param boolean $crosses   Whether the geometries spatially cross.
     */
    public function testCrosses($geometry1, $geometry2, $crosses)
    {
        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'crosses');

        $this->assertSame($crosses, $geometry1->crosses($geometry2));
    }

    /**
     * @return array
     */
    public function providerCrosses()
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
     * @param string  $geometry1 The WKT of the first geometry.
     * @param string  $geometry2 The WKT of the second geometry.
     * @param boolean $within    Whether the first geometry is within the second one.
     */
    public function testWithin($geometry1, $geometry2, $within)
    {
        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->assertSame($within, $geometry1->within($geometry2));
    }

    /**
     * @return array
     */
    public function providerWithin()
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
     * @param string  $geometry1 The WKT of the first geometry.
     * @param string  $geometry2 The WKT of the second geometry.
     * @param boolean $contains  Whether the first geometry contains the second one.
     */
    public function testContains($geometry1, $geometry2, $contains)
    {
        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->assertSame($contains, $geometry1->contains($geometry2));
    }

    /**
     * @return array
     */
    public function providerContains()
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
     * @param string  $geometry1 The WKT of the first geometry.
     * @param string  $geometry2 The WKT of the second geometry.
     * @param boolean $overlaps  Whether the first geometry overlaps the second one.
     */
    public function testOverlaps($geometry1, $geometry2, $overlaps)
    {
        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->assertSame($overlaps, $geometry1->overlaps($geometry2));
    }

    /**
     * @return array
     */
    public function providerOverlaps()
    {
        return [
            ['POLYGON ((1 2, 2 4, 3 3, 2 1, 1 2))', 'POLYGON ((2 2, 2 3, 4 2, 3 1, 2 2))', true],
            ['POLYGON ((1 2, 2 4, 4 3, 2 1, 1 2))', 'POLYGON ((2 2, 2 3, 3 3, 2 2))', false],
        ];
    }

    /**
     * @dataProvider providerRelate
     *
     * @param string  $geometry1 The WKT of the first geometry.
     * @param string  $geometry2 The WKT of the second geometry.
     * @param string  $matrix    The intersection matrix pattern.
     * @param boolean $relate    Whether the first geometry is spatially related to the second one.
     */
    public function testRelate($geometry1, $geometry2, $matrix, $relate)
    {
        if ($this->isMySQL() || $this->isMariaDB()) {
            $this->setExpectedException(GeometryEngineException::class);
        }

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->assertSame($relate, $geometry1->relate($geometry2, $matrix));
    }

    /**
     * @return array
     */
    public function providerRelate()
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
    public function testLocateAlong($geometry, $measure, $result)
    {
        if ($this->isGEOS() || $this->isMySQL() || $this->isMariaDB()) {
            $this->setExpectedException(GeometryEngineException::class);
        }

        $this->assertSame($result, Geometry::fromText($geometry)->locateAlong($measure)->asText());
    }

    /**
     * @return array
     */
    public function providerLocateAlong()
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
     */
    public function testLocateBetween($geometry, $mStart, $mEnd, $result)
    {
        if ($this->isGEOS() || $this->isMySQL() || $this->isMariaDB()) {
            $this->setExpectedException(GeometryEngineException::class);
        }

        $this->assertSame($result, Geometry::fromText($geometry)->locateBetween($mStart, $mEnd)->asText());
    }

    /**
     * @return array
     */
    public function providerLocateBetween()
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
    public function testDistance($geometry1, $geometry2, $distance)
    {
        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->assertSame($distance, $geometry1->distance($geometry2));
    }

    /**
     * @return array
     */
    public function providerDistance()
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
    public function testBuffer($geometry, $distance)
    {
        $geometry = Geometry::fromText($geometry);
        $buffer = $geometry->buffer($distance);

        $this->assertInstanceOf(Polygon::class, $buffer);
        $this->assertTrue($buffer->contains($geometry));

        /** @var Polygon $buffer */
        $ring = $buffer->exteriorRing();

        for ($n = 1; $n <= $ring->numPoints(); $n++) {
            $this->assertSame($distance, $ring->pointN($n)->distance($geometry));
        }
    }

    /**
     * @return array
     */
    public function providerBuffer()
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
    public function testConvexHull($geometry, $result)
    {
        if ($this->isMySQL('< 5.7.6-m16') || $this->isMariaDB('>= 10.0')) {
            $this->setExpectedException(GeometryEngineException::class);
        }

        $geometry = Geometry::fromText($geometry);
        $result   = Geometry::fromText($result);

        $this->assertGeometryEquals($result, $geometry->convexHull());
    }

    /**
     * @return array
     */
    public function providerConvexHull()
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
    public function testIntersection($geometry1, $geometry2, $result)
    {
        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);
        $result    = Geometry::fromText($result);

        $this->skipIfUnsupportedByEngine($geometry1, $geometry2, 'intersection');

        $this->assertGeometryEquals($result, $geometry1->intersection($geometry2));
    }

    /**
     * @return array
     */
    public function providerIntersection()
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
    public function testUnion($geometry1, $geometry2, $result)
    {
        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);
        $result    = Geometry::fromText($result);

        $union = $geometry1->union($geometry2);

        $this->assertSame(get_class($result), get_class($union));
        $this->assertTrue($union->equals($result));
    }

    /**
     * @return array
     */
    public function providerUnion()
    {
        return [
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
    public function testDifference($geometry1, $geometry2, $result)
    {
        if ($this->isMySQL('< 5.7')) {
            $this->markTestSkipped('MySQL 5.6 difference() implementation is very buggy and should not be used.');
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
    public function providerDifference()
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
    public function testSymDifference($geometry1, $geometry2, $result)
    {
        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);
        $result    = Geometry::fromText($result);

        $difference = $geometry1->symDifference($geometry2);

        $this->assertGeometryEquals($result, $difference);
    }

    /**
     * @return array
     */
    public function providerSymDifference()
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
    public function testSnapToGrid($geometry, $size, $result)
    {
        if ($this->isGEOS() || $this->isMySQL() || $this->isMariaDB()) {
            $this->setExpectedException(GeometryEngineException::class);
        }

        $geometry = Geometry::fromText($geometry);
        $result   = Geometry::fromText($result);

        $snapToGrid = $geometry->snapToGrid($size);

        $this->assertGeometryEquals($result, $snapToGrid);
    }

    /**
     * @return array
     */
    public function providerSnapToGrid()
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
    public function testSimplify($geometry, $tolerance, $result)
    {
        if ($this->isMySQL('< 5.7.6-m16') || $this->isMariaDB('>= 10.0') || $this->isSpatiaLite('< 4.1.0')) {
            $this->setExpectedException(GeometryEngineException::class);
        }

        $geometry = Geometry::fromText($geometry);
        $result   = Geometry::fromText($result);

        $this->assertGeometryEquals($result, $geometry->simplify($tolerance));
    }

    /**
     * @return array
     */
    public function providerSimplify()
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
     */
    public function testMaxDistance($geometry1, $geometry2, $maxDistance)
    {
        if ($this->isGEOS() || $this->isMySQL() || $this->isMariaDB() || $this->isSpatiaLite()) {
            $this->setExpectedException(GeometryEngineException::class);
        }

        $geometry1 = Geometry::fromText($geometry1);
        $geometry2 = Geometry::fromText($geometry2);

        $this->assertSame($maxDistance, $geometry1->maxDistance($geometry2));
    }

    /**
     * @return array
     */
    public function providerMaxDistance()
    {
        return [
            ['POINT (0 0)', 'LINESTRING (2 0, 0 2)', 2.0],
            ['POINT (0 0)', 'LINESTRING (1 1, 0 4)', 4.0],
            ['LINESTRING (1 1, 3 1)', 'LINESTRING (4 1, 6 1)', 5.0],
        ];
    }
}
