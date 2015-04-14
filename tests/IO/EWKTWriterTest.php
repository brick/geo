<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\GeometryCollection;
use Brick\Geo\IO\EWKTWriter;

/**
 * Unit tests for class EWKTWriter.
 */
class EWKTWriterTest extends EWKTAbstractTest
{
    /**
     * @dataProvider providerPrettyPrint
     *
     * @param boolean $prettyPrint Whether to set the prettyPrint parameter.
     * @param string  $ewkt        The expected result EWKT.
     */
    public function testPrettyPrint($prettyPrint, $ewkt)
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint($prettyPrint);

        $lineString = $this->createLineString([[1, 2, 3, 4], [5, 6, 7, 8]], true, true, 4326);

        $this->assertSame($ewkt, $writer->write($lineString));
    }

    /**
     * @return array
     */
    public function providerPrettyPrint()
    {
        return [
            [false, 'SRID=4326;LINESTRING ZM(1 2 3 4,5 6 7 8)'],
            [true, 'SRID=4326; LINESTRING ZM (1 2 3 4, 5 6 7 8)']
        ];
    }

    /**
     * @dataProvider providerPointWKT
     *
     * @param string  $wkt        The expected WKT.
     * @param array   $coords     The Point coordinates.
     * @param boolean $is3D       Whether the Point has a Z coordinate.
     * @param boolean $isMeasured Whether the Point has a M coordinate.
     */
    public function testWritePoint($wkt, array $coords, $is3D, $isMeasured)
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $point = $this->createPoint($coords, $is3D, $isMeasured, 4326);
        $this->assertSame($this->toEWKT($wkt, 4326), $writer->write($point));
    }

    /**
     * @dataProvider providerLineStringWKT
     *
     * @param string  $wkt        The expected WKT.
     * @param array   $coords     The LineString coordinates.
     * @param boolean $is3D       Whether the LineString has Z coordinates.
     * @param boolean $isMeasured Whether the LineString has M coordinates.
     */
    public function testWriteLineString($wkt, array $coords, $is3D, $isMeasured)
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $lineString = $this->createLineString($coords, $is3D, $isMeasured, 4326);
        $this->assertSame($this->toEWKT($wkt, 4326), $writer->write($lineString));
    }

    /**
     * @dataProvider providerPolygonWKT
     *
     * @param string  $wkt        The expected WKT.
     * @param array   $coords     The Polygon coordinates.
     * @param boolean $is3D       Whether the Polygon has Z coordinates.
     * @param boolean $isMeasured Whether the Polygon has M coordinates.
     */
    public function testWritePolygon($wkt, array $coords, $is3D, $isMeasured)
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $polygon = $this->createPolygon($coords, $is3D, $isMeasured, 4326);
        $this->assertSame($this->toEWKT($wkt, 4326), $writer->write($polygon));
    }

    /**
     * @dataProvider providerMultiPointWKT
     *
     * @param string  $wkt        The expected WKT.
     * @param array   $coords     The MultiPoint coordinates.
     * @param boolean $is3D       Whether the MultiPoint has Z coordinates.
     * @param boolean $isMeasured Whether the MultiPoint has M coordinates.
     */
    public function testWriteMultiPoint($wkt, array $coords, $is3D, $isMeasured)
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $multiPoint = $this->createMultiPoint($coords, $is3D, $isMeasured, 4326);
        $this->assertSame($this->toEWKT($wkt, 4326), $writer->write($multiPoint));
    }

    /**
     * @dataProvider providerMultiLineStringWKT
     *
     * @param string  $wkt        The expected WKT.
     * @param array   $coords     The MultiLineString coordinates.
     * @param boolean $is3D       Whether the MultiLineString has Z coordinates.
     * @param boolean $isMeasured Whether the MultiLineString has M coordinates.
     */
    public function testWriteMultiLineString($wkt, array $coords, $is3D, $isMeasured)
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $multiLineString = $this->createMultiLineString($coords, $is3D, $isMeasured, 4326);
        $this->assertSame($this->toEWKT($wkt, 4326), $writer->write($multiLineString));
    }

    /**
     * @dataProvider providerMultiPolygonWKT
     *
     * @param string  $wkt        The expected WKT.
     * @param array   $coords     The MultiPolygon coordinates.
     * @param boolean $is3D       Whether the MultiPolygon has Z coordinates.
     * @param boolean $isMeasured Whether the MultiPolygon has M coordinates.
     */
    public function testWriteMultiPolygon($wkt, array $coords, $is3D, $isMeasured)
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $multiPolygon = $this->createMultiPolygon($coords, $is3D, $isMeasured, 4326);
        $this->assertSame($this->toEWKT($wkt, 4326), $writer->write($multiPolygon));
    }

    /**
     * @dataProvider providerGeometryCollectionWKT
     *
     * @param string  $wkt        The expected WKT.
     * @param array   $coords     The GeometryCollection coordinates.
     * @param boolean $is3D       Whether the GeometryCollection has Z coordinates.
     * @param boolean $isMeasured Whether the GeometryCollection has M coordinates.
     */
    public function testWriteGeometryCollection($wkt, array $coords, $is3D, $isMeasured)
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $point = $this->createPoint($coords[0], $is3D, $isMeasured, 4326);
        $lineString = $this->createLineString($coords[1], $is3D, $isMeasured, 4326);

        $geometryCollection = GeometryCollection::factory([$point, $lineString]);
        $this->assertSame($this->toEWKT($wkt, 4326), $writer->write($geometryCollection));
    }

    /**
     * @dataProvider providerPolyhedralSurfaceWKT
     *
     * @param string  $wkt        The expected WKT.
     * @param array   $coords     The PolyhedralSurface coordinates.
     * @param boolean $is3D       Whether the PolyhedralSurface has Z coordinates.
     * @param boolean $isMeasured Whether the PolyhedralSurface has M coordinates.
     */
    public function testWritePolyhedralSurface($wkt, array $coords, $is3D, $isMeasured)
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $polyhedralSurface = $this->createPolyhedralSurface($coords, $is3D, $isMeasured, 4326);
        $this->assertSame($this->toEWKT($wkt, 4326), $writer->write($polyhedralSurface));
    }
}
