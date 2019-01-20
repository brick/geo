<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\IO\GeoJSONWriter;
use Brick\Geo\MultiPoint;
use Brick\Geo\Point;

class GeoJSONWriterTest extends GeoJSONAbstractTest
{
    /**
     * @dataProvider providerReadGeometryPoint
     *
     * @param string $geojson    The GeoJSON to read.
     * @param array  $coords     The expected Point coordinates.
     * @param bool   $is3D       Whether the resulting Point has a Z coordinate.
     * @param bool   $isMeasured Whether the resulting Point has a M coordinate.
     *
     * @return void
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\GeometryIOException
     */
    public function testReadGeometryPoint(string $geojson, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $isEmpty = empty($coords);
        $cs = new CoordinateSystem($is3D, $isMeasured);

        if ($isEmpty) {
            $geometry = new Point($cs);
        } else {
            $geometry = new Point($cs, ...$coords);
        }

        $geometryGeoJSON = (new GeoJSONWriter())->write($geometry);

        $this->assertEquals($geojson, $geometryGeoJSON);
    }

    /**
     * @return \Generator
     */
    public function providerReadGeometryPoint() : \Generator
    {
        foreach ($this->providerGeometryPointGeoJSON() as [$geojson, $coords, $is3D, $isMeasured]) {
            yield [$geojson, $coords, $is3D, $isMeasured];
        }
    }

    /**
     * @dataProvider providerReadGeometryMultiPoint
     *
     * @param string $geojson    The GeoJSON to read.
     * @param array  $coords     The expected Point coordinates.
     * @param bool   $is3D       Whether the resulting Point has a Z coordinate.
     * @param bool   $isMeasured Whether the resulting Point has a M coordinate.
     *
     * @return void
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\GeometryIOException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    public function testReadGeometryMultiPoint(string $geojson, array $coords, bool $is3D, bool $isMeasured) : void
    {
        $isEmpty = empty($coords);
        $cs = new CoordinateSystem($is3D, $isMeasured);

        if ($isEmpty) {
            $geometry = new MultiPoint($cs);
        } else {
            $points = [];
            foreach ($coords as $pointCoords) {
                $points[] = new Point($cs, ...$pointCoords);
            }
            $geometry = new MultiPoint($cs, ...$points);
        }

        $geometryGeoJSON = (new GeoJSONWriter())->write($geometry);

        $this->assertEquals($geojson, $geometryGeoJSON);
    }

    /**
     * @return \Generator
     */
    public function providerReadGeometryMultiPoint() : \Generator
    {
        foreach ($this->providerGeometryMultiPointGeoJSON() as [$geojson, $coords, $is3D, $isMeasured]) {
            yield [$geojson, $coords, $is3D, $isMeasured];
        }
    }
}