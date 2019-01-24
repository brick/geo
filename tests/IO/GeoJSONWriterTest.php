<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\GeoJSONReader;
use Brick\Geo\IO\GeoJSONWriter;

class GeoJSONWriterTest extends GeoJSONAbstractTest
{
    /**
     * @dataProvider providerWriteGeometry
     * @dataProvider providerWriteFeatureCollection
     *
     * @param string $geojson The GeoJSON to read.
     *
     * @return void
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function testWriteGeometry(string $geojson) : void
    {
        $geometry = (new GeoJSONReader())->read($geojson);
        $geometryGeoJSON = (new GeoJSONWriter())->write($geometry);

        $this->assertEquals($geojson, $geometryGeoJSON);
    }

    /**
     * @return \Generator
     */
    public function providerWriteGeometry() : \Generator
    {
        foreach ($this->providerGeometryGeoJSON() as [$geojson, $coords, $is3D]) {
            yield [$geojson];
        }
    }

    /**
     * @return \Generator
     */
    public function providerWriteFeatureCollection() : \Generator
    {
        foreach ($this->providerFeatureCollectionGeoJSON() as [$geojson, $coords, $is3D]) {
            yield [$geojson];
        }
    }
}