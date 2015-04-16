<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\GeometryCollection;
use Brick\Geo\IO\WKTWriter;
use Brick\Geo\MultiLineString;

/**
 * Unit tests for class WKTWriter.
 */
class WKTWriterTest extends WKTAbstractTest
{
    /**
     * @dataProvider providerPrettyPrint
     *
     * @param boolean $is3D        Whether to use Z coordinates.
     * @param boolean $prettyPrint Whether to set the prettyPrint parameter.
     * @param string  $wkt         The expected result WKT.
     */
    public function testPrettyPrint($is3D, $prettyPrint, $wkt)
    {
        $writer = new WKTWriter();
        $writer->setPrettyPrint($prettyPrint);

        $cs = CoordinateSystem::create($is3D, false);

        $point = $this->createPoint([1, 2, 3], $cs);
        $lineString1 = $this->createLineString([[1, 2, 3], [4, 5, 6]], $cs);
        $lineString2 = $this->createLineString([[2, 3, 4], [5, 6, 7]], $cs);
        $multiLineString = MultiLineString::factory([$lineString1, $lineString2]);
        $geometryCollection = GeometryCollection::factory([$point, $multiLineString]);

        $this->assertSame($wkt, $writer->write($geometryCollection));
    }

    /**
     * @return array
     */
    public function providerPrettyPrint()
    {
        return [
            [false, false, 'GEOMETRYCOLLECTION(POINT(1 2),MULTILINESTRING((1 2,4 5),(2 3,5 6)))'],
            [false, true, 'GEOMETRYCOLLECTION (POINT (1 2), MULTILINESTRING ((1 2, 4 5), (2 3, 5 6)))'],

            [true, false, 'GEOMETRYCOLLECTION Z(POINT Z(1 2 3),MULTILINESTRING Z((1 2 3,4 5 6),(2 3 4,5 6 7)))'],
            [true, true, 'GEOMETRYCOLLECTION Z (POINT Z (1 2 3), MULTILINESTRING Z ((1 2 3, 4 5 6), (2 3 4, 5 6 7)))'],
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
        $writer = new WKTWriter();
        $writer->setPrettyPrint(false);

        $cs = CoordinateSystem::create($is3D, $isMeasured);
        $point = $this->createPoint($coords, $cs);

        $this->assertSame($wkt, $writer->write($point));
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
        $writer = new WKTWriter();
        $writer->setPrettyPrint(false);

        $cs = CoordinateSystem::create($is3D, $isMeasured);
        $lineString = $this->createLineString($coords, $cs);

        $this->assertSame($wkt, $writer->write($lineString));
    }

    /**
     * @dataProvider providerCircularStringWKT
     *
     * @param string  $wkt        The expected WKT.
     * @param array   $coords     The CircularString coordinates.
     * @param boolean $is3D       Whether the CircularString has Z coordinates.
     * @param boolean $isMeasured Whether the CircularString has M coordinates.
     */
    public function testWriteCircularString($wkt, array $coords, $is3D, $isMeasured)
    {
        $writer = new WKTWriter();
        $writer->setPrettyPrint(false);

        $cs = CoordinateSystem::create($is3D, $isMeasured);
        $lineString = $this->createCircularString($coords, $cs);

        $this->assertSame($wkt, $writer->write($lineString));
    }

    /**
     * @dataProvider providerCompoundCurveWKT
     *
     * @param string  $wkt        The expected WKT.
     * @param array   $coords     The CompoundCurve coordinates.
     * @param boolean $is3D       Whether the CompoundCurve has Z coordinates.
     * @param boolean $isMeasured Whether the CompoundCurve has M coordinates.
     */
    public function testWriteCompoundCurve($wkt, array $coords, $is3D, $isMeasured)
    {
        $writer = new WKTWriter();
        $writer->setPrettyPrint(false);

        $cs = CoordinateSystem::create($is3D, $isMeasured);
        $compoundCurve = $this->createCompoundCurve($coords, $cs);

        $this->assertSame($wkt, $writer->write($compoundCurve));
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
        $writer = new WKTWriter();
        $writer->setPrettyPrint(false);

        $cs = CoordinateSystem::create($is3D, $isMeasured);
        $polygon = $this->createPolygon($coords, $cs);

        $this->assertSame($wkt, $writer->write($polygon));
    }

    /**
     * @dataProvider providerTriangleWKT
     *
     * @param string  $wkt        The expected WKT.
     * @param array   $coords     The Triangle coordinates.
     * @param boolean $is3D       Whether the Triangle has Z coordinates.
     * @param boolean $isMeasured Whether the Triangle has M coordinates.
     */
    public function testWriteTriangle($wkt, array $coords, $is3D, $isMeasured)
    {
        $writer = new WKTWriter();
        $writer->setPrettyPrint(false);

        $cs = CoordinateSystem::create($is3D, $isMeasured);
        $triangle = $this->createTriangle($coords, $cs);

        $this->assertSame($wkt, $writer->write($triangle));
    }

    /**
     * @dataProvider providerCurvePolygonWKT
     *
     * @param string  $wkt        The expected WKT.
     * @param array   $coords     The Polygon coordinates.
     * @param boolean $is3D       Whether the Polygon has Z coordinates.
     * @param boolean $isMeasured Whether the Polygon has M coordinates.
     */
    public function testWriteCurvePolygon($wkt, array $coords, $is3D, $isMeasured)
    {
        $writer = new WKTWriter();
        $writer->setPrettyPrint(false);

        $cs = CoordinateSystem::create($is3D, $isMeasured);
        $polygon = $this->createCurvePolygon($coords, $cs);

        $this->assertSame($wkt, $writer->write($polygon));
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
        $writer = new WKTWriter();
        $writer->setPrettyPrint(false);

        $cs = CoordinateSystem::create($is3D, $isMeasured);
        $polyhedralSurface = $this->createPolyhedralSurface($coords, $cs);

        $this->assertSame($wkt, $writer->write($polyhedralSurface));
    }

    /**
     * @dataProvider providerTINWKT
     *
     * @param string  $wkt        The expected WKT.
     * @param array   $coords     The TIN coordinates.
     * @param boolean $is3D       Whether the TIN has Z coordinates.
     * @param boolean $isMeasured Whether the TIN has M coordinates.
     */
    public function testWriteTIN($wkt, array $coords, $is3D, $isMeasured)
    {
        $writer = new WKTWriter();
        $writer->setPrettyPrint(false);

        $cs = CoordinateSystem::create($is3D, $isMeasured);
        $tin = $this->createTIN($coords, $cs);

        $this->assertSame($wkt, $writer->write($tin));
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
        $writer = new WKTWriter();
        $writer->setPrettyPrint(false);

        $cs = CoordinateSystem::create($is3D, $isMeasured);
        $multiPoint = $this->createMultiPoint($coords, $cs);

        $this->assertSame($wkt, $writer->write($multiPoint));
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
        $writer = new WKTWriter();
        $writer->setPrettyPrint(false);

        $cs = CoordinateSystem::create($is3D, $isMeasured);
        $multiLineString = $this->createMultiLineString($coords, $cs);

        $this->assertSame($wkt, $writer->write($multiLineString));
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
        $writer = new WKTWriter();
        $writer->setPrettyPrint(false);

        $cs = CoordinateSystem::create($is3D, $isMeasured);
        $multiPolygon = $this->createMultiPolygon($coords, $cs);

        $this->assertSame($wkt, $writer->write($multiPolygon));
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
        $writer = new WKTWriter();
        $writer->setPrettyPrint(false);

        $cs = CoordinateSystem::create($is3D, $isMeasured);

        if ($coords) {
            $point = $this->createPoint($coords[0], $cs);
            $lineString = $this->createLineString($coords[1], $cs);
            $geometries = [$point, $lineString];
        } else {
            $geometries = [];
        }

        $geometryCollection = GeometryCollection::create($geometries, $cs);
        $this->assertSame($wkt, $writer->write($geometryCollection));
    }

    /**
     * @dataProvider providerWriteEmptyGeometryCollection
     *
     * @param string $wkt The WKT to test.
     */
    public function testWriteEmptyGeometryCollection($wkt)
    {
        $writer = new WKTWriter();
        $geometry = GeometryCollection::fromText($wkt);

        $this->assertSame($wkt, $writer->write($geometry));
    }

    /**
     * @return array
     */
    public function providerWriteEmptyGeometryCollection()
    {
        return [
            ['GEOMETRYCOLLECTION EMPTY'],
            ['GEOMETRYCOLLECTION (POINT EMPTY)'],
            ['GEOMETRYCOLLECTION (POINT EMPTY, LINESTRING EMPTY, POLYGON EMPTY)']
        ];
    }
}
