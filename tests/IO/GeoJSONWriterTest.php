<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\GeoJSONReader;
use Brick\Geo\IO\GeoJSONWriter;

class GeoJSONWriterTest extends GeoJSONAbstractTest
{
    /**
     * @dataProvider providerReadGeometry
     * @dataProvider providerReadFeatureCollection
     *
     * @param string $geojson The GeoJSON to read.
     *
     * @return void
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\GeometryIOException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    public function testReadGeometry(string $geojson) : void
    {
        $geometry = (new GeoJSONReader())->read($geojson);
        $geometryGeoJSON = (new GeoJSONWriter())->write($geometry);

        $this->assertEquals(strtoupper($geojson), strtoupper($geometryGeoJSON));
    }

    /**
     * @return \Generator
     */
    public function providerReadGeometry() : \Generator
    {
        foreach ($this->providerGeometryGeoJSON() as [$geojson, $coords, $is3D, $isMeasured]) {
            yield [$geojson];
        }
    }

    /**
     * @return \Generator
     */
    public function providerReadFeatureCollection() : \Generator
    {
        foreach ($this->providerFeatureCollectionGeoJSON() as [$geojson, $coords, $is3D, $isMeasured]) {
            yield [$geojson];
        }
    }
}