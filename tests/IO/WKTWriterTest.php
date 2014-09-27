<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\GeometryCollection;
use Brick\Geo\IO\WKTWriter;

/**
 * Unit tests for class WKTWriter.
 */
class WKTWriterTest extends WKTAbstractTest
{
    /**
     * @dataProvider providerPointWKT
     *
     * @param string $wkt         The expected WKT.
     * @param array  $coords      The Point coordinates.
     * @param boolean $is3D       Whether the Point has a Z coordinate.
     * @param boolean $isMeasured Whether the Point has a M coordinate.
     */
    public function testWritePoint($wkt, array $coords, $is3D, $isMeasured)
    {
        $point = self::createPoint($coords, $is3D, $isMeasured);
        $this->assertSame($this->prettyPrint($wkt), (new WKTWriter())->write($point));
    }

    /**
     * @dataProvider providerLineStringWKT
     *
     * @param string $wkt         The expected WKT.
     * @param array  $coords      The LineString coordinates.
     * @param boolean $is3D       Whether the LineString has Z coordinates.
     * @param boolean $isMeasured Whether the LineString has M coordinates.
     */
    public function testWriteLineString($wkt, array $coords, $is3D, $isMeasured)
    {
        $lineString = self::createLineString($coords, $is3D, $isMeasured);
        $this->assertSame($this->prettyPrint($wkt), (new WKTWriter())->write($lineString));
    }

    /**
     * @dataProvider providerPolygonWKT
     *
     * @param string $wkt         The expected WKT.
     * @param array  $coords      The Polygon coordinates.
     * @param boolean $is3D       Whether the Polygon has Z coordinates.
     * @param boolean $isMeasured Whether the Polygon has M coordinates.
     */
    public function testWritePolygon($wkt, array $coords, $is3D, $isMeasured)
    {
        $polygon = self::createPolygon($coords, $is3D, $isMeasured);
        $this->assertSame($this->prettyPrint($wkt), (new WKTWriter())->write($polygon));
    }

    /**
     * @dataProvider providerMultiPointWKT
     *
     * @param string $wkt         The expected WKT.
     * @param array  $coords      The MultiPoint coordinates.
     * @param boolean $is3D       Whether the MultiPoint has Z coordinates.
     * @param boolean $isMeasured Whether the MultiPoint has M coordinates.
     */
    public function testWriteMultiPoint($wkt, array $coords, $is3D, $isMeasured)
    {
        $multiPoint = self::createMultiPoint($coords, $is3D, $isMeasured);
        $this->assertSame($this->prettyPrint($wkt), (new WKTWriter())->write($multiPoint));
    }

    /**
     * @dataProvider providerMultiLineStringWKT
     *
     * @param string $wkt         The expected WKT.
     * @param array  $coords      The MultiLineString coordinates.
     * @param boolean $is3D       Whether the MultiLineString has Z coordinates.
     * @param boolean $isMeasured Whether the MultiLineString has M coordinates.
     */
    public function testWriteMultiLineString($wkt, array $coords, $is3D, $isMeasured)
    {
        $multiLineString = self::createMultiLineString($coords, $is3D, $isMeasured);
        $this->assertSame($this->prettyPrint($wkt), (new WKTWriter())->write($multiLineString));
    }

    /**
     * @dataProvider providerMultiPolygonWKT
     *
     * @param string $wkt         The expected WKT.
     * @param array  $coords      The MultiPolygon coordinates.
     * @param boolean $is3D       Whether the MultiPolygon has Z coordinates.
     * @param boolean $isMeasured Whether the MultiPolygon has M coordinates.
     */
    public function testWriteMultiPolygon($wkt, array $coords, $is3D, $isMeasured)
    {
        $multiPolygon = self::createMultiPolygon($coords, $is3D, $isMeasured);
        $this->assertSame($this->prettyPrint($wkt), (new WKTWriter())->write($multiPolygon));
    }

    /**
     * @dataProvider providerGeometryCollectionWKT
     *
     * @param string $wkt         The expected WKT.
     * @param array  $coords      The GeometryCollection coordinates.
     * @param boolean $is3D       Whether the GeometryCollection has Z coordinates.
     * @param boolean $isMeasured Whether the GeometryCollection has M coordinates.
     */
    public function testWriteGeometryCollection($wkt, array $coords, $is3D, $isMeasured)
    {
        $point = self::createPoint($coords[0], $is3D, $isMeasured);
        $lineString = self::createLineString($coords[1], $is3D, $isMeasured);

        $geometryCollection = GeometryCollection::factory([$point, $lineString]);
        $this->assertSame($this->prettyPrint($wkt), (new WKTWriter())->write($geometryCollection));
    }

    /**
     * @param string $wkt
     *
     * @return string
     */
    private function prettyPrint($wkt)
    {
        $wkt = preg_replace('/([A-Z])\(/', '$1 (', $wkt);
        $wkt = str_replace(',', ', ', $wkt);

        return $wkt;
    }
}
