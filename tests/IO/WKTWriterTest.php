<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\GeometryCollection;
use Brick\Geo\IO\WKTWriter;
use Brick\Geo\Tests\AbstractTestCase;

/**
 * Unit tests for class WKTWriter.
 */
class WKTWriterTest extends AbstractTestCase
{
    /**
     * @dataProvider providerWritePoint
     *
     * @param array  $coords The Point coordinates.
     * @param string $wkt    The expected WKT.
     */
    public function testWritePoint(array $coords, $wkt)
    {
        $point = self::createPoint($coords);
        $this->assertSame($wkt, (new WKTWriter())->write($point));
    }

    /**
     * @return array
     */
    public function providerWritePoint()
    {
        return [
            [[1, 2], 'POINT (1 2)'],
            [[2, 3, 4], 'POINT Z (2 3 4)'],
            [[3, 4, null, 5], 'POINT M (3 4 5)'],
            [[4, 5, 6, 7], 'POINT ZM (4 5 6 7)'],
        ];
    }

    /**
     * @dataProvider providerWriteLineString
     *
     * @param array  $coords The LineString coordinates.
     * @param string $wkt    The expected WKT.
     */
    public function testWriteLineString(array $coords, $wkt)
    {
        $lineString = self::createLineString($coords);
        $this->assertSame($wkt, (new WKTWriter())->write($lineString));
    }

    /**
     * @return array
     */
    public function providerWriteLineString()
    {
        return [
            [[[0, 0], [1, 2], [3, 4]], 'LINESTRING (0 0, 1 2, 3 4)'],
            [[[0, 1, 2], [1, 2, 3], [2, 3, 4]], 'LINESTRING Z (0 1 2, 1 2 3, 2 3 4)'],
            [[[1, 2, null, 3], [2, 3, null, 4], [3, 4, null, 5]], 'LINESTRING M (1 2 3, 2 3 4, 3 4 5)'],
            [[[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7]], 'LINESTRING ZM (2 3 4 5, 3 4 5 6, 4 5 6 7)']
        ];
    }

    /**
     * @dataProvider providerWritePolygon
     *
     * @param array  $coords The Polygon coordinates.
     * @param string $wkt    The expected WKT.
     */
    public function testWritePolygon(array $coords, $wkt)
    {
        $polygon = self::createPolygon($coords);
        $this->assertSame($wkt, (new WKTWriter())->write($polygon));
    }

    /**
     * @return array
     */
    public function providerWritePolygon()
    {
        return [
            [[[[0, 0], [1, 2], [3, 4], [0, 0]]], 'POLYGON ((0 0, 1 2, 3 4, 0 0))'],
            [[[[0, 1, 2], [1, 2, 3], [2, 3, 4], [0, 1, 2]]], 'POLYGON Z ((0 1 2, 1 2 3, 2 3 4, 0 1 2))'],
            [[[[1, 2, null, 3], [2, 3, null, 4], [3, 4, null, 5], [1, 2, null, 3]]], 'POLYGON M ((1 2 3, 2 3 4, 3 4 5, 1 2 3))'],
            [[[[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7], [2, 3, 4, 5]]], 'POLYGON ZM ((2 3 4 5, 3 4 5 6, 4 5 6 7, 2 3 4 5))'],

            [[[[0, 0], [2, 0], [0, 2], [0, 0]], [[0, 0], [1, 0], [0, 1], [0, 0]]], 'POLYGON ((0 0, 2 0, 0 2, 0 0), (0 0, 1 0, 0 1, 0 0))'],
            [[[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]], [[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]], 'POLYGON Z ((0 0 1, 2 0 1, 0 2 1, 0 0 1), (0 0 2, 1 0 2, 0 1 2, 0 0 2))'],
            [[[[0, 0, null, 1], [2, 0, null, 1], [0, 2, null, 1], [0, 0, null, 1]], [[0, 0, null, 2], [1, 0, null, 2], [0, 1, null, 2], [0, 0, null, 2]]], 'POLYGON M ((0 0 1, 2 0 1, 0 2 1, 0 0 1), (0 0 2, 1 0 2, 0 1 2, 0 0 2))'],
            [[[[0, 0, 1, 2], [2, 0, 1, 2], [0, 2, 1, 2], [0, 0, 1, 2]], [[0, 0, 1, 2], [1, 0, 1, 2], [0, 1, 1, 2], [0, 0, 1, 2]]], 'POLYGON ZM ((0 0 1 2, 2 0 1 2, 0 2 1 2, 0 0 1 2), (0 0 1 2, 1 0 1 2, 0 1 1 2, 0 0 1 2))']
        ];
    }

    /**
     * @dataProvider providerWriteMultiPoint
     *
     * @param array  $coords The MultiPoint coordinates.
     * @param string $wkt    The expected WKT.
     */
    public function testWriteMultiPoint(array $coords, $wkt)
    {
        $multiPoint = self::createMultiPoint($coords);
        $this->assertSame($wkt, (new WKTWriter())->write($multiPoint));
    }

    /**
     * @return array
     */
    public function providerWriteMultiPoint()
    {
        return [
            [[[0, 0], [1, 2], [3, 4]], 'MULTIPOINT (0 0, 1 2, 3 4)'],
            [[[0, 1, 2], [1, 2, 3], [2, 3, 4]], 'MULTIPOINT Z (0 1 2, 1 2 3, 2 3 4)'],
            [[[1, 2, null, 3], [2, 3, null, 4], [3, 4, null, 5]], 'MULTIPOINT M (1 2 3, 2 3 4, 3 4 5)'],
            [[[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7]], 'MULTIPOINT ZM (2 3 4 5, 3 4 5 6, 4 5 6 7)']
        ];
    }

    /**
     * @dataProvider providerWriteMultiLineString
     *
     * @param array  $coords The MultiLineString coordinates.
     * @param string $wkt    The expected WKT.
     */
    public function testWriteMultiLineString(array $coords, $wkt)
    {
        $multiLineString = self::createMultiLineString($coords);
        $this->assertSame($wkt, (new WKTWriter())->write($multiLineString));
    }

    /**
     * @return array
     */
    public function providerWriteMultiLineString()
    {
        return [
            [[[[0, 0], [1, 2], [3, 4], [0, 0]]], 'MULTILINESTRING ((0 0, 1 2, 3 4, 0 0))'],
            [[[[0, 1, 2], [1, 2, 3], [2, 3, 4], [0, 1, 2]]], 'MULTILINESTRING Z ((0 1 2, 1 2 3, 2 3 4, 0 1 2))'],
            [[[[1, 2, null, 3], [2, 3, null, 4], [3, 4, null, 5], [1, 2, null, 3]]], 'MULTILINESTRING M ((1 2 3, 2 3 4, 3 4 5, 1 2 3))'],
            [[[[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7], [2, 3, 4, 5]]], 'MULTILINESTRING ZM ((2 3 4 5, 3 4 5 6, 4 5 6 7, 2 3 4 5))'],

            [[[[0, 0], [2, 0], [0, 2], [0, 0]], [[0, 0], [1, 0], [0, 1], [0, 0]]], 'MULTILINESTRING ((0 0, 2 0, 0 2, 0 0), (0 0, 1 0, 0 1, 0 0))'],
            [[[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]], [[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]], 'MULTILINESTRING Z ((0 0 1, 2 0 1, 0 2 1, 0 0 1), (0 0 2, 1 0 2, 0 1 2, 0 0 2))'],
            [[[[0, 0, null, 1], [2, 0, null, 1], [0, 2, null, 1], [0, 0, null, 1]], [[0, 0, null, 2], [1, 0, null, 2], [0, 1, null, 2], [0, 0, null, 2]]], 'MULTILINESTRING M ((0 0 1, 2 0 1, 0 2 1, 0 0 1), (0 0 2, 1 0 2, 0 1 2, 0 0 2))'],
            [[[[0, 0, 1, 2], [2, 0, 1, 2], [0, 2, 1, 2], [0, 0, 1, 2]], [[0, 0, 1, 2], [1, 0, 1, 2], [0, 1, 1, 2], [0, 0, 1, 2]]], 'MULTILINESTRING ZM ((0 0 1 2, 2 0 1 2, 0 2 1 2, 0 0 1 2), (0 0 1 2, 1 0 1 2, 0 1 1 2, 0 0 1 2))']
        ];
    }

    /**
     * @dataProvider providerWriteMultiPolygon
     *
     * @param array  $coords The MultiPolygon coordinates.
     * @param string $wkt    The expected WKT.
     */
    public function testWriteMultiPolygon(array $coords, $wkt)
    {
        $multiPolygon = self::createMultiPolygon($coords);
        $this->assertSame($wkt, (new WKTWriter())->write($multiPolygon));
    }

    /**
     * @return array
     */
    public function providerWriteMultiPolygon()
    {
        return [
            [[[[[0, 0], [1, 2], [3, 4], [0, 0]]]], 'MULTIPOLYGON (((0 0, 1 2, 3 4, 0 0)))'],
            [[[[[0, 1, 2], [1, 2, 3], [2, 3, 4], [0, 1, 2]]]], 'MULTIPOLYGON Z (((0 1 2, 1 2 3, 2 3 4, 0 1 2)))'],
            [[[[[1, 2, null, 3], [2, 3, null, 4], [3, 4, null, 5], [1, 2, null, 3]]]], 'MULTIPOLYGON M (((1 2 3, 2 3 4, 3 4 5, 1 2 3)))'],
            [[[[[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7], [2, 3, 4, 5]]]], 'MULTIPOLYGON ZM (((2 3 4 5, 3 4 5 6, 4 5 6 7, 2 3 4 5)))'],

            [[[[[0, 0], [2, 0], [0, 2], [0, 0]]], [[[0, 0], [1, 0], [0, 1], [0, 0]]]], 'MULTIPOLYGON (((0 0, 2 0, 0 2, 0 0)), ((0 0, 1 0, 0 1, 0 0)))'],
            [[[[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]]], [[[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]]], 'MULTIPOLYGON Z (((0 0 1, 2 0 1, 0 2 1, 0 0 1)), ((0 0 2, 1 0 2, 0 1 2, 0 0 2)))'],
            [[[[[0, 0, null, 1], [2, 0, null, 1], [0, 2, null, 1], [0, 0, null, 1]]], [[[0, 0, null, 2], [1, 0, null, 2], [0, 1, null, 2], [0, 0, null, 2]]]], 'MULTIPOLYGON M (((0 0 1, 2 0 1, 0 2 1, 0 0 1)), ((0 0 2, 1 0 2, 0 1 2, 0 0 2)))'],
            [[[[[0, 0, 1, 2], [2, 0, 1, 2], [0, 2, 1, 2], [0, 0, 1, 2]]], [[[0, 0, 1, 2], [1, 0, 1, 2], [0, 1, 1, 2], [0, 0, 1, 2]]]], 'MULTIPOLYGON ZM (((0 0 1 2, 2 0 1 2, 0 2 1 2, 0 0 1 2)), ((0 0 1 2, 1 0 1 2, 0 1 1 2, 0 0 1 2)))']
        ];
    }

    /**
     * @dataProvider providerWriteGeometryCollection
     *
     * @param boolean $is3D       Whether to include Z-coordinates.
     * @param boolean $isMeasured Whether to include M-coordinates.
     * @param string  $wkt        The expected WKT.
     */
    public function testWriteGeometryCollection($is3D, $isMeasured, $wkt)
    {
        $a = [1, 2, $is3D ? 3 : null, $isMeasured ? 4 : null];
        $b = [2, 3, $is3D ? 4 : null, $isMeasured ? 5 : null];
        $c = [3, 4, $is3D ? 5 : null, $isMeasured ? 6 : null];

        $point = self::createPoint($a);
        $lineString = self::createLineString([$b, $c]);

        $geometryCollection = GeometryCollection::factory([$point, $lineString]);
        $this->assertSame($wkt, (new WKTWriter())->write($geometryCollection));
    }

    /**
     * @return array
     */
    public function providerWriteGeometryCollection()
    {
        return [
            [false, false, 'GEOMETRYCOLLECTION (POINT (1 2), LINESTRING (2 3, 3 4))'],
            [true, false, 'GEOMETRYCOLLECTION Z (POINT Z (1 2 3), LINESTRING Z (2 3 4, 3 4 5))'],
            [false, true, 'GEOMETRYCOLLECTION M (POINT M (1 2 4), LINESTRING M (2 3 5, 3 4 6))'],
            [true, true, 'GEOMETRYCOLLECTION ZM (POINT ZM (1 2 3 4), LINESTRING ZM (2 3 4 5, 3 4 5 6))']
        ];
    }
}
