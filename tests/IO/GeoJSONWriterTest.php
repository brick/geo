<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\GeoJSONReader;
use Brick\Geo\IO\GeoJSONWriter;
use Brick\Geo\Point;

class GeoJSONWriterTest extends GeoJSONAbstractTest
{
    /**
     * @dataProvider providerGeometryGeoJSON
     * @dataProvider providerFeatureGeoJSON
     * @dataProvider providerFeatureCollectionGeoJSON
     *
     * @param string $geojson The GeoJSON to read.
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function testWriteGeometry(string $geojson) : void
    {
        $geometry = (new GeoJSONReader())->read($geojson);
        $geometryGeoJSON = (new GeoJSONWriter())->write($geometry);

        self::assertSame($geojson, $geometryGeoJSON);
    }

    /**
     * @throws \Brick\Geo\Exception\GeometryIOException
     */
    public function testPrettyPrint() : void
    {
        $writer = new GeoJSONWriter(true);
        $geoJSONOutput = $writer->write(Point::xyz(1, 2, 3));

        $expectedGeoJSON = <<<EOL
        {
            "type": "Point",
            "coordinates": [
                1,
                2,
                3
            ]
        }
        EOL;

        self::assertSame($expectedGeoJSON, $geoJSONOutput);
    }
}
