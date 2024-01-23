<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\GeoJSONReader;
use Brick\Geo\IO\GeoJSONWriter;
use Brick\Geo\Point;
use Brick\Geo\Polygon;
use PHPUnit\Framework\Attributes\DataProvider;

class GeoJSONWriterTest extends GeoJSONAbstractTestCase
{
    #[DataProvider('providerGeometryGeoJSON')]
    #[DataProvider('providerFeatureGeoJSON')]
    #[DataProvider('providerFeatureCollectionGeoJSON')]
    public function testWriteGeometry(string $geojson) : void
    {
        $geometry = (new GeoJSONReader())->read($geojson);
        $geometryGeoJSON = (new GeoJSONWriter())->write($geometry);

        self::assertSame($geojson, $geometryGeoJSON);
    }

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

    public function testWriteGeometryWithM() : void
    {
        $writer = new GeoJSONWriter(true);

        // the M coordinate must be ignored
        $geoJSONOutput = $writer->write(Point::xym(1, 2, 3));

        $expectedGeoJSON = <<<EOL
        {
            "type": "Point",
            "coordinates": [
                1,
                2
            ]
        }
        EOL;

        self::assertSame($expectedGeoJSON, $geoJSONOutput);
    }

    public function testWriteGeometryWithBbox() : void
    {
        $writer = new GeoJSONWriter(true, true);

        $polygon = Polygon::fromText('POLYGON((2 2, 1 5, 2 8, 3 5, 2 2))');
        $geoJSONOutput = $writer->write($polygon);

        $expectedGeoJSON = <<<EOL
        {
            "type": "Polygon",
            "coordinates": [
                [
                    [
                        2,
                        2
                    ],
                    [
                        1,
                        5
                    ],
                    [
                        2,
                        8
                    ],
                    [
                        3,
                        5
                    ],
                    [
                        2,
                        2
                    ]
                ]
            ],
            "bbox": [
                1,
                2,
                3,
                8
            ]
        }
        EOL;

        self::assertSame($expectedGeoJSON, $geoJSONOutput);
    }
}
