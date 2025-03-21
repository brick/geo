<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Io;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\GeometryCollection;
use Brick\Geo\Io\WktWriter;
use Brick\Geo\MultiLineString;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class WktWriter.
 */
class WktWriterTest extends WktAbstractTestCase
{
    /**
     * @param bool   $is3D        Whether to use Z coordinates.
     * @param bool   $prettyPrint Whether to set the prettyPrint parameter.
     * @param string $wkt         The expected result WKT.
     */
    #[DataProvider('providerPrettyPrint')]
    public function testPrettyPrint(bool $is3D, bool $prettyPrint, string $wkt) : void
    {
        $writer = new WktWriter(prettyPrint: $prettyPrint);

        $cs = new CoordinateSystem($is3D, false);

        $one  = $is3D ? [1, 2, 3] : [1, 2];
        $two  = $is3D ? [2, 3, 4] : [2, 3];
        $four = $is3D ? [4, 5, 6] : [4, 5];
        $five = $is3D ? [5, 6, 7] : [5, 6];

        $point = $this->createPoint($one, $cs);
        $lineString1 = $this->createLineString([$one, $four], $cs);
        $lineString2 = $this->createLineString([$two, $five], $cs);
        $multiLineString = MultiLineString::of($lineString1, $lineString2);
        $geometryCollection = GeometryCollection::of($point, $multiLineString);

        self::assertSame($wkt, $writer->write($geometryCollection));
    }

    public static function providerPrettyPrint() : array
    {
        return [
            [false, false, 'GEOMETRYCOLLECTION(POINT(1 2),MULTILINESTRING((1 2,4 5),(2 3,5 6)))'],
            [false, true, 'GEOMETRYCOLLECTION (POINT (1 2), MULTILINESTRING ((1 2, 4 5), (2 3, 5 6)))'],

            [true, false, 'GEOMETRYCOLLECTION Z(POINT Z(1 2 3),MULTILINESTRING Z((1 2 3,4 5 6),(2 3 4,5 6 7)))'],
            [true, true, 'GEOMETRYCOLLECTION Z (POINT Z (1 2 3), MULTILINESTRING Z ((1 2 3, 4 5 6), (2 3 4, 5 6 7)))'],
        ];
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The Point coordinates.
     * @param bool   $is3D       Whether the Point has a Z coordinate.
     * @param bool   $isMeasured Whether the Point has a M coordinate.
     */
    #[DataProvider('providerPointWkt')]
    public function testWritePoint(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new WktWriter(prettyPrint: false);

        $cs = new CoordinateSystem($is3D, $isMeasured);
        $point = $this->createPoint($coords, $cs);

        self::assertSame($wkt, $writer->write($point));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The LineString coordinates.
     * @param bool   $is3D       Whether the LineString has Z coordinates.
     * @param bool   $isMeasured Whether the LineString has M coordinates.
     */
    #[DataProvider('providerLineStringWkt')]
    public function testWriteLineString(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new WktWriter(prettyPrint: false);

        $cs = new CoordinateSystem($is3D, $isMeasured);
        $lineString = $this->createLineString($coords, $cs);

        self::assertSame($wkt, $writer->write($lineString));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The CircularString coordinates.
     * @param bool   $is3D       Whether the CircularString has Z coordinates.
     * @param bool   $isMeasured Whether the CircularString has M coordinates.
     */
    #[DataProvider('providerCircularStringWkt')]
    public function testWriteCircularString(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new WktWriter(prettyPrint: false);

        $cs = new CoordinateSystem($is3D, $isMeasured);
        $lineString = $this->createCircularString($coords, $cs);

        self::assertSame($wkt, $writer->write($lineString));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The CompoundCurve coordinates.
     * @param bool   $is3D       Whether the CompoundCurve has Z coordinates.
     * @param bool   $isMeasured Whether the CompoundCurve has M coordinates.
     */
    #[DataProvider('providerCompoundCurveWkt')]
    public function testWriteCompoundCurve(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new WktWriter(prettyPrint: false);

        $cs = new CoordinateSystem($is3D, $isMeasured);
        $compoundCurve = $this->createCompoundCurve($coords, $cs);

        self::assertSame($wkt, $writer->write($compoundCurve));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The Polygon coordinates.
     * @param bool   $is3D       Whether the Polygon has Z coordinates.
     * @param bool   $isMeasured Whether the Polygon has M coordinates.
     */
    #[DataProvider('providerPolygonWkt')]
    public function testWritePolygon(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new WktWriter(prettyPrint: false);

        $cs = new CoordinateSystem($is3D, $isMeasured);
        $polygon = $this->createPolygon($coords, $cs);

        self::assertSame($wkt, $writer->write($polygon));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The Triangle coordinates.
     * @param bool   $is3D       Whether the Triangle has Z coordinates.
     * @param bool   $isMeasured Whether the Triangle has M coordinates.
     */
    #[DataProvider('providerTriangleWkt')]
    public function testWriteTriangle(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new WktWriter(prettyPrint: false);

        $cs = new CoordinateSystem($is3D, $isMeasured);
        $triangle = $this->createTriangle($coords, $cs);

        self::assertSame($wkt, $writer->write($triangle));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The Polygon coordinates.
     * @param bool   $is3D       Whether the Polygon has Z coordinates.
     * @param bool   $isMeasured Whether the Polygon has M coordinates.
     */
    #[DataProvider('providerCurvePolygonWkt')]
    public function testWriteCurvePolygon(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new WktWriter(prettyPrint: false);

        $cs = new CoordinateSystem($is3D, $isMeasured);
        $polygon = $this->createCurvePolygon($coords, $cs);

        self::assertSame($wkt, $writer->write($polygon));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The PolyhedralSurface coordinates.
     * @param bool   $is3D       Whether the PolyhedralSurface has Z coordinates.
     * @param bool   $isMeasured Whether the PolyhedralSurface has M coordinates.
     */
    #[DataProvider('providerPolyhedralSurfaceWkt')]
    public function testWritePolyhedralSurface(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new WktWriter(prettyPrint: false);

        $cs = new CoordinateSystem($is3D, $isMeasured);
        $polyhedralSurface = $this->createPolyhedralSurface($coords, $cs);

        self::assertSame($wkt, $writer->write($polyhedralSurface));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The TIN coordinates.
     * @param bool   $is3D       Whether the TIN has Z coordinates.
     * @param bool   $isMeasured Whether the TIN has M coordinates.
     */
    #[DataProvider('providerTinWkt')]
    public function testWriteTin(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new WktWriter(prettyPrint: false);

        $cs = new CoordinateSystem($is3D, $isMeasured);
        $tin = $this->createTin($coords, $cs);

        self::assertSame($wkt, $writer->write($tin));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The MultiPoint coordinates.
     * @param bool   $is3D       Whether the MultiPoint has Z coordinates.
     * @param bool   $isMeasured Whether the MultiPoint has M coordinates.
     */
    #[DataProvider('providerMultiPointWkt')]
    public function testWriteMultiPoint(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new WktWriter(prettyPrint: false);

        $cs = new CoordinateSystem($is3D, $isMeasured);
        $multiPoint = $this->createMultiPoint($coords, $cs);

        self::assertSame($wkt, $writer->write($multiPoint));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The MultiLineString coordinates.
     * @param bool   $is3D       Whether the MultiLineString has Z coordinates.
     * @param bool   $isMeasured Whether the MultiLineString has M coordinates.
     */
    #[DataProvider('providerMultiLineStringWkt')]
    public function testWriteMultiLineString(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new WktWriter(prettyPrint: false);

        $cs = new CoordinateSystem($is3D, $isMeasured);
        $multiLineString = $this->createMultiLineString($coords, $cs);

        self::assertSame($wkt, $writer->write($multiLineString));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The MultiPolygon coordinates.
     * @param bool   $is3D       Whether the MultiPolygon has Z coordinates.
     * @param bool   $isMeasured Whether the MultiPolygon has M coordinates.
     */
    #[DataProvider('providerMultiPolygonWkt')]
    public function testWriteMultiPolygon(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new WktWriter(prettyPrint: false);

        $cs = new CoordinateSystem($is3D, $isMeasured);
        $multiPolygon = $this->createMultiPolygon($coords, $cs);

        self::assertSame($wkt, $writer->write($multiPolygon));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The GeometryCollection coordinates.
     * @param bool   $is3D       Whether the GeometryCollection has Z coordinates.
     * @param bool   $isMeasured Whether the GeometryCollection has M coordinates.
     */
    #[DataProvider('providerGeometryCollectionWkt')]
    public function testWriteGeometryCollection(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new WktWriter(prettyPrint: false);

        $cs = new CoordinateSystem($is3D, $isMeasured);

        if ($coords) {
            $point = $this->createPoint($coords[0], $cs);
            $lineString = $this->createLineString($coords[1], $cs);
            $geometries = [$point, $lineString];
        } else {
            $geometries = [];
        }

        $geometryCollection = new GeometryCollection($cs, ...$geometries);
        self::assertSame($wkt, $writer->write($geometryCollection));
    }

    #[DataProvider('providerWriteEmptyGeometryCollection')]
    public function testWriteEmptyGeometryCollection(string $wkt) : void
    {
        $writer = new WktWriter();
        $geometry = GeometryCollection::fromText($wkt);

        self::assertSame($wkt, $writer->write($geometry));
    }

    public static function providerWriteEmptyGeometryCollection() : array
    {
        return [
            ['GEOMETRYCOLLECTION EMPTY'],
            ['GEOMETRYCOLLECTION (POINT EMPTY)'],
            ['GEOMETRYCOLLECTION (POINT EMPTY, LINESTRING EMPTY, POLYGON EMPTY)']
        ];
    }
}
