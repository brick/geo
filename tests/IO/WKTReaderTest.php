<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Point;
use Brick\Geo\LineString;
use Brick\Geo\Polygon;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPolygon;
use Brick\Geo\GeometryCollection;
use Brick\Geo\IO\WKTReader;
use Brick\Geo\Tests\AbstractTestCase;

/**
 * Unit tests for class WKTReader.
 */
class WKTReaderTest extends AbstractTestCase
{
    /**
     * @dataProvider providerReadPoint
     *
     * @param string  $wkt        The WKT to read.
     * @param array   $coords     The expected Point coordinates.
     * @param boolean $is3D       Whether the resulting Point has a Z coordinate.
     * @param boolean $isMeasured Whether the resulting Point has a M coordinate.
     */
    public function testReadPoint($wkt, array $coords, $is3D, $isMeasured)
    {
        /** @var Point $point */
        $point = (new WKTReader())->read($wkt, 4326);
        $this->assertPointEquals($coords, $is3D, $isMeasured, 4326, $point);
    }

    /**
     * @dataProvider providerReadPoint
     *
     * @param string  $wkt        The WKT to read.
     * @param array   $coords     The expected Point coordinates.
     * @param boolean $is3D       Whether the resulting Point has a Z coordinate.
     * @param boolean $isMeasured Whether the resulting Point has a M coordinate.
     */
    public function testReadPointAltered($wkt, array $coords, $is3D, $isMeasured)
    {
        $this->testReadPoint($this->alter($wkt), $coords, $is3D, $isMeasured);
    }

    /**
     * @return array
     */
    public function providerReadPoint()
    {
        return [
            ['POINT(1 2)', [1, 2], false, false],
            ['POINT Z(2 3 4)', [2, 3, 4], true, false],
            ['POINT M(3 4 5)', [3, 4, 5], false, true],
            ['POINT ZM(4 5 6 7)', [4, 5, 6, 7], true, true],
        ];
    }

    /**
     * @dataProvider providerReadLineString
     *
     * @param string $wkt         The WKT to read.
     * @param array  $coords      The expected LineString coordinates.
     * @param boolean $is3D       Whether the resulting LineString has a Z coordinate.
     * @param boolean $isMeasured Whether the resulting LineString has a M coordinate.
     */
    public function testReadLineString($wkt, array $coords, $is3D, $isMeasured)
    {
        /** @var LineString $lineString */
        $lineString = (new WKTReader())->read($wkt);
        $this->assertLineStringEquals($coords, $is3D, $isMeasured, $lineString);
    }

    /**
     * @dataProvider providerReadLineString
     *
     * @param string $wkt         The WKT to read.
     * @param array  $coords      The expected LineString coordinates.
     * @param boolean $is3D       Whether the resulting LineString has Z coordinates.
     * @param boolean $isMeasured Whether the resulting LineString has M coordinates.
     */
    public function testReadLineStringAltered($wkt, array $coords, $is3D, $isMeasured)
    {
        $this->testReadLineString($this->alter($wkt), $coords, $is3D, $isMeasured);
    }

    /**
     * @return array
     */
    public function providerReadLineString()
    {
        return [
            ['LINESTRING(0 0,1 2,3 4)', [[0, 0], [1, 2], [3, 4]], false, false],
            ['LINESTRING Z(0 1 2,1 2 3,2 3 4)', [[0, 1, 2], [1, 2, 3], [2, 3, 4]], true, false],
            ['LINESTRING M(1 2 3,2 3 4,3 4 5)', [[1, 2, 3], [2, 3, 4], [3, 4, 5]], false, true],
            ['LINESTRING ZM(2 3 4 5,3 4 5 6,4 5 6 7)', [[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7]], true, true]
        ];
    }

    /**
     * @dataProvider providerReadPolygon
     *
     * @param string $wkt         The WKT to read.
     * @param array  $coords      The expected Polygon coordinates.
     * @param boolean $is3D       Whether the resulting Polygon has Z coordinates.
     * @param boolean $isMeasured Whether the resulting Polygon has M coordinates.
     */
    public function testReadPolygon($wkt, array $coords, $is3D, $isMeasured)
    {
        /** @var Polygon $polygon */
        $polygon = (new WKTReader())->read($wkt);
        $this->assertPolygonEquals($coords, $is3D, $isMeasured, $polygon);
    }

    /**
     * @dataProvider providerReadPolygon
     *
     * @param string $wkt         The WKT to read.
     * @param array  $coords      The expected Polygon coordinates.
     * @param boolean $is3D       Whether the resulting Polygon has Z coordinates.
     * @param boolean $isMeasured Whether the resulting Polygon has M coordinates.
     */
    public function testReadPolygonAltered($wkt, array $coords, $is3D, $isMeasured)
    {
        $this->testReadPolygon($this->alter($wkt), $coords, $is3D, $isMeasured);
    }

    /**
     * @return array
     */
    public function providerReadPolygon()
    {
        return [
            ['POLYGON((0 0,1 2,3 4,0 0))', [[[0, 0], [1, 2], [3, 4], [0, 0]]], false, false],
            ['POLYGON Z((0 1 2,1 2 3,2 3 4,0 1 2))', [[[0, 1, 2], [1, 2, 3], [2, 3, 4], [0, 1, 2]]], true, false],
            ['POLYGON M((1 2 3,2 3 4,3 4 5,1 2 3))', [[[1, 2, 3], [2, 3, 4], [3, 4, 5], [1, 2, 3]]], false, true],
            ['POLYGON ZM((2 3 4 5,3 4 5 6,4 5 6 7,2 3 4 5))', [[[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7], [2, 3, 4, 5]]], true, true],

            ['POLYGON((0 0,2 0,0 2,0 0),(0 0,1 0,0 1,0 0))', [[[0, 0], [2, 0], [0, 2], [0, 0]], [[0, 0], [1, 0], [0, 1], [0, 0]]], false, false],
            ['POLYGON Z((0 0 1,2 0 1,0 2 1,0 0 1),(0 0 2,1 0 2,0 1 2,0 0 2))', [[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]], [[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]], true, false],
            ['POLYGON M((0 0 1,2 0 1,0 2 1,0 0 1),(0 0 2,1 0 2,0 1 2,0 0 2))', [[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]], [[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]], false, true],
            ['POLYGON ZM((0 0 1 2,2 0 1 2,0 2 1 2,0 0 1 2),(0 0 1 2,1 0 1 2,0 1 1 2,0 0 1 2))', [[[0, 0, 1, 2], [2, 0, 1, 2], [0, 2, 1, 2], [0, 0, 1, 2]], [[0, 0, 1, 2], [1, 0, 1, 2], [0, 1, 1, 2], [0, 0, 1, 2]]], true, true]
        ];
    }

    /**
     * @dataProvider providerReadMultiPoint
     *
     * @param string $wkt         The WKT to read.
     * @param array  $coords      The expected MultiPoint coordinates.
     * @param boolean $is3D       Whether the resulting MultiPoint has Z coordinates.
     * @param boolean $isMeasured Whether the resulting MultiPoint has M coordinates.
     */
    public function testReadMultiPoint($wkt, array $coords, $is3D, $isMeasured)
    {
        /** @var MultiPoint $multiPoint */
        $multiPoint = (new WKTReader())->read($wkt);
        $this->assertMultiPointEquals($coords, $is3D, $isMeasured, $multiPoint);
    }

    /**
     * @dataProvider providerReadMultiPoint
     *
     * @param string $wkt         The WKT to read.
     * @param array  $coords      The expected MultiPoint coordinates.
     * @param boolean $is3D       Whether the resulting MultiPoint has Z coordinates.
     * @param boolean $isMeasured Whether the resulting MultiPoint has M coordinates.
     */
    public function testReadMultiPointAltered($wkt, array $coords, $is3D, $isMeasured)
    {
        $this->testReadMultiPoint($this->alter($wkt), $coords, $is3D, $isMeasured);
    }

    /**
     * @return array
     */
    public function providerReadMultiPoint()
    {
        return [
            ['MULTIPOINT (0 0,1 2,3 4)', [[0, 0], [1, 2], [3, 4]], false, false],
            ['MULTIPOINT Z (0 1 2,1 2 3,2 3 4)', [[0, 1, 2], [1, 2, 3], [2, 3, 4]], true, false],
            ['MULTIPOINT M (1 2 3,2 3 4,3 4 5)', [[1, 2, 3], [2, 3, 4], [3, 4, 5]], false, true],
            ['MULTIPOINT ZM (2 3 4 5,3 4 5 6,4 5 6 7)', [[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7]], true, true]
        ];
    }

    /**
     * @dataProvider providerReadMultiLineString
     *
     * @param string $wkt         The WKT to read.
     * @param array  $coords      The expected MultiLineString coordinates.
     * @param boolean $is3D       Whether the resulting MultiLineString has Z coordinates.
     * @param boolean $isMeasured Whether the resulting MultiLineString has M coordinates.
     */
    public function testReadMultiLineString($wkt, array $coords, $is3D, $isMeasured)
    {
        /** @var MultiLineString $multiLineString */
        $multiLineString = (new WKTReader())->read($wkt);
        $this->assertMultiLineStringEquals($coords, $is3D, $isMeasured, $multiLineString);
    }

    /**
     * @dataProvider providerReadMultiLineString
     *
     * @param string $wkt         The WKT to read.
     * @param array  $coords      The expected MultiLineString coordinates.
     * @param boolean $is3D       Whether the resulting MultiLineString has Z coordinates.
     * @param boolean $isMeasured Whether the resulting MultiLineString has M coordinates.
     */
    public function testReadMultiLineStringAltered($wkt, array $coords, $is3D, $isMeasured)
    {
        $this->testReadMultiLineString($this->alter($wkt), $coords, $is3D, $isMeasured);
    }

    /**
     * @return array
     */
    public function providerReadMultiLineString()
    {
        return [
            ['MULTILINESTRING ((0 0,1 2,3 4,0 0))', [[[0, 0], [1, 2], [3, 4], [0, 0]]], false, false],
            ['MULTILINESTRING Z ((0 1 2,1 2 3,2 3 4,0 1 2))', [[[0, 1, 2], [1, 2, 3], [2, 3, 4], [0, 1, 2]]], true, false],
            ['MULTILINESTRING M ((1 2 3,2 3 4,3 4 5,1 2 3))', [[[1, 2, 3], [2, 3, 4], [3, 4, 5], [1, 2, 3]]], false, true],
            ['MULTILINESTRING ZM ((2 3 4 5,3 4 5 6,4 5 6 7,2 3 4 5))', [[[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7], [2, 3, 4, 5]]], true, true],

            ['MULTILINESTRING ((0 0,2 0,0 2,0 0),(0 0,1 0,0 1,0 0))', [[[0, 0], [2, 0], [0, 2], [0, 0]], [[0, 0], [1, 0], [0, 1], [0, 0]]], false, false],
            ['MULTILINESTRING Z ((0 0 1,2 0 1,0 2 1,0 0 1),(0 0 2,1 0 2,0 1 2,0 0 2))', [[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]], [[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]], true, false],
            ['MULTILINESTRING M ((0 0 1,2 0 1,0 2 1,0 0 1),(0 0 2,1 0 2,0 1 2,0 0 2))', [[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]], [[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]], false, true],
            ['MULTILINESTRING ZM ((0 0 1 2,2 0 1 2,0 2 1 2,0 0 1 2),(0 0 1 2,1 0 1 2,0 1 1 2,0 0 1 2))', [[[0, 0, 1, 2], [2, 0, 1, 2], [0, 2, 1, 2], [0, 0, 1, 2]], [[0, 0, 1, 2], [1, 0, 1, 2], [0, 1, 1, 2], [0, 0, 1, 2]]], true, true]
        ];
    }

    /**
     * @dataProvider providerReadMultiPolygon
     *
     * @param string $wkt         The WKT to read.
     * @param array  $coords      The expected MultiPolygon coordinates.
     * @param boolean $is3D       Whether the resulting MultiPolygon has Z coordinates.
     * @param boolean $isMeasured Whether the resulting MultiPolygon has M coordinates.
     */
    public function testReadMultiPolygon($wkt, array $coords, $is3D, $isMeasured)
    {
        /** @var MultiPolygon $multiPolygon */
        $multiPolygon = (new WKTReader())->read($wkt);
        $this->assertMultiPolygonEquals($coords, $is3D, $isMeasured, $multiPolygon);
    }

    /**
     * @dataProvider providerReadMultiPolygon
     *
     * @param string $wkt         The WKT to read.
     * @param array  $coords      The expected MultiPolygon coordinates.
     * @param boolean $is3D       Whether the resulting MultiPolygon has Z coordinates.
     * @param boolean $isMeasured Whether the resulting MultiPolygon has M coordinates.
     */
    public function testReadMultiPolygonAltered($wkt, array $coords, $is3D, $isMeasured)
    {
        $this->testReadMultiPolygon($this->alter($wkt), $coords, $is3D, $isMeasured);
    }

    /**
     * @return array
     */
    public function providerReadMultiPolygon()
    {
        return [
            ['MULTIPOLYGON (((0 0,1 2,3 4,0 0)))', [[[[0, 0], [1, 2], [3, 4], [0, 0]]]], false, false],
            ['MULTIPOLYGON Z (((0 1 2,1 2 3,2 3 4,0 1 2)))', [[[[0, 1, 2], [1, 2, 3], [2, 3, 4], [0, 1, 2]]]], true, false],
            ['MULTIPOLYGON M (((1 2 3,2 3 4,3 4 5,1 2 3)))', [[[[1, 2, 3], [2, 3, 4], [3, 4, 5], [1, 2, 3]]]], false, true],
            ['MULTIPOLYGON ZM (((2 3 4 5,3 4 5 6,4 5 6 7,2 3 4 5)))', [[[[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7], [2, 3, 4, 5]]]], true, true],

            ['MULTIPOLYGON (((0 0,2 0, 0 2,0 0)),((0 0,1 0,0 1,0 0)))', [[[[0, 0], [2, 0], [0, 2], [0, 0]]], [[[0, 0], [1, 0], [0, 1], [0, 0]]]], false, false],
            ['MULTIPOLYGON Z (((0 0 1,2 0 1,0 2 1,0 0 1)),((0 0 2,1 0 2,0 1 2,0 0 2)))', [[[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]]], [[[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]]], true, false],
            ['MULTIPOLYGON M (((0 0 1,2 0 1,0 2 1,0 0 1)),((0 0 2,1 0 2,0 1 2,0 0 2)))', [[[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]]], [[[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]]], false, true],
            ['MULTIPOLYGON ZM (((0 0 1 2,2 0 1 2,0 2 1 2,0 0 1 2)),((0 0 1 2,1 0 1 2,0 1 1 2,0 0 1 2)))', [[[[0, 0, 1, 2], [2, 0, 1, 2], [0, 2, 1, 2], [0, 0, 1, 2]]], [[[0, 0, 1, 2], [1, 0, 1, 2], [0, 1, 1, 2], [0, 0, 1, 2]]]], true, true]
        ];
    }

    /**
     * @dataProvider providerReadGeometryCollection
     *
     * @param string $wkt         The WKT to read.
     * @param boolean $is3D       Whether the resulting GeometryCollection has Z coordinates.
     * @param boolean $isMeasured Whether the resulting GeometryCollection has M coordinates.
     */
    public function testReadGeometryCollection($wkt, $is3D, $isMeasured)
    {
        /** @var GeometryCollection $geometryCollection */
        $geometryCollection = (new WKTReader())->read($wkt);

        $a = [1, 2];
        $b = [2, 3];
        $c = [3, 4];

        if ($is3D) {
            $a[] = 3;
            $b[] = 4;
            $c[] = 5;
        }

        if ($isMeasured) {
            $a[] = 4;
            $b[] = 5;
            $c[] = 6;
        }

        $this->assertInstanceOf(GeometryCollection::class, $geometryCollection);

        /** @var Point $point */
        $point = $geometryCollection->geometryN(1);
        $this->assertPointEquals($a, $is3D, $isMeasured, 0, $point);

        /** @var LineString $lineString */
        $lineString = $geometryCollection->geometryN(2);
        $this->assertLineStringEquals([$b, $c], $is3D, $isMeasured, $lineString);
    }

    /**
     * @dataProvider providerReadGeometryCollection
     *
     * @param string $wkt         The WKT to read.
     * @param boolean $is3D       Whether the resulting GeometryCollection has Z coordinates.
     * @param boolean $isMeasured Whether the resulting GeometryCollection has M coordinates.
     */
    public function testReadGeometryCollectionAltered($wkt, $is3D, $isMeasured)
    {
        $this->testReadGeometryCollection($this->alter($wkt), $is3D, $isMeasured);
    }

    /**
     * @return array
     */
    public function providerReadGeometryCollection()
    {
        return [
            ['GEOMETRYCOLLECTION (POINT (1 2),LINESTRING (2 3,3 4))', false, false],
            ['GEOMETRYCOLLECTION Z (POINT Z (1 2 3),LINESTRING Z (2 3 4,3 4 5))', true, false],
            ['GEOMETRYCOLLECTION M (POINT M (1 2 4),LINESTRING M (2 3 5,3 4 6))', false, true],
            ['GEOMETRYCOLLECTION ZM (POINT ZM (1 2 3 4),LINESTRING ZM (2 3 4 5,3 4 5 6))', true, true]
        ];
    }

    /**
     * Adds extra spaces to a WKT string, and changes its case.
     *
     * The result is still a valid WKT string, that the reader should be able to handle.
     *
     * @param string $wkt
     *
     * @return string
     */
    private function alter($wkt)
    {
        $search = [' ', '(', ')', ','];
        $replace = [];

        foreach ($search as $char) {
            $replace[] = " $char ";
        }

        $wkt = str_replace($search, $replace, $wkt);

        return strtolower(" $wkt ");
    }
}
