<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\GeometryCollection;
use Brick\Geo\IO\EWKTWriter;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class EWKTWriter.
 */
class EWKTWriterTest extends EWKTAbstractTestCase
{
    /**
     * @param bool   $prettyPrint Whether to set the prettyPrint parameter.
     * @param string $ewkt        The expected result EWKT.
     */
    #[DataProvider('providerPrettyPrint')]
    public function testPrettyPrint(bool $prettyPrint, string $ewkt) : void
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint($prettyPrint);

        $lineString = $this->createLineString([[1, 2, 3, 4], [5, 6, 7, 8]], CoordinateSystem::xyzm(4326));

        self::assertSame($ewkt, $writer->write($lineString));
    }

    public static function providerPrettyPrint() : array
    {
        return [
            [false, 'SRID=4326;LINESTRING ZM(1 2 3 4,5 6 7 8)'],
            [true, 'SRID=4326; LINESTRING ZM (1 2 3 4, 5 6 7 8)']
        ];
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The Point coordinates.
     * @param bool   $is3D       Whether the Point has a Z coordinate.
     * @param bool   $isMeasured Whether the Point has a M coordinate.
     */
    #[DataProvider('providerPointWKT')]
    public function testWritePoint(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $cs = new CoordinateSystem($is3D, $isMeasured, 4326);
        $point = $this->createPoint($coords, $cs);

        self::assertSame($this->toEWKT($wkt, 4326), $writer->write($point));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The LineString coordinates.
     * @param bool   $is3D       Whether the LineString has Z coordinates.
     * @param bool   $isMeasured Whether the LineString has M coordinates.
     */
    #[DataProvider('providerLineStringWKT')]
    public function testWriteLineString(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $cs = new CoordinateSystem($is3D, $isMeasured, 4326);
        $lineString = $this->createLineString($coords, $cs);

        self::assertSame($this->toEWKT($wkt, 4326), $writer->write($lineString));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The CircularString coordinates.
     * @param bool   $is3D       Whether the CircularString has Z coordinates.
     * @param bool   $isMeasured Whether the CircularString has M coordinates.
     */
    #[DataProvider('providerCircularStringWKT')]
    public function testWriteCircularString(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $cs = new CoordinateSystem($is3D, $isMeasured, 4326);
        $lineString = $this->createCircularString($coords, $cs);

        self::assertSame($this->toEWKT($wkt, 4326), $writer->write($lineString));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The CompoundCurve coordinates.
     * @param bool   $is3D       Whether the CompoundCurve has Z coordinates.
     * @param bool   $isMeasured Whether the CompoundCurve has M coordinates.
     */
    #[DataProvider('providerCompoundCurveWKT')]
    public function testWriteCompoundCurve(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $cs = new CoordinateSystem($is3D, $isMeasured, 4326);
        $compoundCurve = $this->createCompoundCurve($coords, $cs);

        self::assertSame($this->toEWKT($wkt, 4326), $writer->write($compoundCurve));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The Polygon coordinates.
     * @param bool   $is3D       Whether the Polygon has Z coordinates.
     * @param bool   $isMeasured Whether the Polygon has M coordinates.
     */
    #[DataProvider('providerPolygonWKT')]
    public function testWritePolygon(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $cs = new CoordinateSystem($is3D, $isMeasured, 4326);
        $polygon = $this->createPolygon($coords, $cs);

        self::assertSame($this->toEWKT($wkt, 4326), $writer->write($polygon));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The Triangle coordinates.
     * @param bool   $is3D       Whether the Triangle has Z coordinates.
     * @param bool   $isMeasured Whether the Triangle has M coordinates.
     */
    #[DataProvider('providerTriangleWKT')]
    public function testWriteTriangle(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $cs = new CoordinateSystem($is3D, $isMeasured, 4326);
        $triangle = $this->createTriangle($coords, $cs);

        self::assertSame($this->toEWKT($wkt, 4326), $writer->write($triangle));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The Polygon coordinates.
     * @param bool   $is3D       Whether the Polygon has Z coordinates.
     * @param bool   $isMeasured Whether the Polygon has M coordinates.
     */
    #[DataProvider('providerCurvePolygonWKT')]
    public function testWriteCurvePolygon(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $cs = new CoordinateSystem($is3D, $isMeasured, 4326);
        $polygon = $this->createCurvePolygon($coords, $cs);

        self::assertSame($this->toEWKT($wkt, 4326), $writer->write($polygon));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The PolyhedralSurface coordinates.
     * @param bool   $is3D       Whether the PolyhedralSurface has Z coordinates.
     * @param bool   $isMeasured Whether the PolyhedralSurface has M coordinates.
     */
    #[DataProvider('providerPolyhedralSurfaceWKT')]
    public function testWritePolyhedralSurface(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $cs = new CoordinateSystem($is3D, $isMeasured, 4326);
        $polyhedralSurface = $this->createPolyhedralSurface($coords, $cs);

        self::assertSame($this->toEWKT($wkt, 4326), $writer->write($polyhedralSurface));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The TIN coordinates.
     * @param bool   $is3D       Whether the TIN has Z coordinates.
     * @param bool   $isMeasured Whether the TIN has M coordinates.
     */
    #[DataProvider('providerTinWKT')]
    public function testWriteTin(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $cs = new CoordinateSystem($is3D, $isMeasured, 4326);
        $tin = $this->createTin($coords, $cs);

        self::assertSame($this->toEWKT($wkt, 4326), $writer->write($tin));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The MultiPoint coordinates.
     * @param bool   $is3D       Whether the MultiPoint has Z coordinates.
     * @param bool   $isMeasured Whether the MultiPoint has M coordinates.
     */
    #[DataProvider('providerMultiPointWKT')]
    public function testWriteMultiPoint(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $cs = new CoordinateSystem($is3D, $isMeasured, 4326);
        $multiPoint = $this->createMultiPoint($coords, $cs);

        self::assertSame($this->toEWKT($wkt, 4326), $writer->write($multiPoint));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The MultiLineString coordinates.
     * @param bool   $is3D       Whether the MultiLineString has Z coordinates.
     * @param bool   $isMeasured Whether the MultiLineString has M coordinates.
     */
    #[DataProvider('providerMultiLineStringWKT')]
    public function testWriteMultiLineString(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $cs = new CoordinateSystem($is3D, $isMeasured, 4326);
        $multiLineString = $this->createMultiLineString($coords, $cs);

        self::assertSame($this->toEWKT($wkt, 4326), $writer->write($multiLineString));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The MultiPolygon coordinates.
     * @param bool   $is3D       Whether the MultiPolygon has Z coordinates.
     * @param bool   $isMeasured Whether the MultiPolygon has M coordinates.
     */
    #[DataProvider('providerMultiPolygonWKT')]
    public function testWriteMultiPolygon(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $cs = new CoordinateSystem($is3D, $isMeasured, 4326);
        $multiPolygon = $this->createMultiPolygon($coords, $cs);

        self::assertSame($this->toEWKT($wkt, 4326), $writer->write($multiPolygon));
    }

    /**
     * @param string $wkt        The expected WKT.
     * @param array  $coords     The GeometryCollection coordinates.
     * @param bool   $is3D       Whether the GeometryCollection has Z coordinates.
     * @param bool   $isMeasured Whether the GeometryCollection has M coordinates.
     */
    #[DataProvider('providerGeometryCollectionWKT')]
    public function testWriteGeometryCollection(string $wkt, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $writer = new EWKTWriter();
        $writer->setPrettyPrint(false);

        $cs = new CoordinateSystem($is3D, $isMeasured, 4326);

        if ($coords) {
            $point = $this->createPoint($coords[0], $cs);
            $lineString = $this->createLineString($coords[1], $cs);
            $geometries = [$point, $lineString];
        } else {
            $geometries = [];
        }

        $geometryCollection = new GeometryCollection($cs, ...$geometries);
        self::assertSame($this->toEWKT($wkt, 4326), $writer->write($geometryCollection));
    }
}
