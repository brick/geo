<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\GeometryCollection;
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

        $expectedGeoJSON = <<<'EOF'
        {
            "type": "Point",
            "coordinates": [
                1,
                2,
                3
            ]
        }
        EOF;

        self::assertSame($expectedGeoJSON, $geoJSONOutput);
    }

    public function testWriteGeometryWithM() : void
    {
        $writer = new GeoJSONWriter(true);

        // the M coordinate must be ignored
        $geoJSONOutput = $writer->write(Point::xym(1, 2, 3));

        $expectedGeoJSON = <<<'EOF'
        {
            "type": "Point",
            "coordinates": [
                1,
                2
            ]
        }
        EOF;

        self::assertSame($expectedGeoJSON, $geoJSONOutput);
    }

    public function testWriteGeometryWithBbox() : void
    {
        $writer = new GeoJSONWriter(true, true);

        $polygon = Polygon::fromText('POLYGON((2 2, 1 5, 2 8, 3 5, 2 2))');
        $geoJSONOutput = $writer->write($polygon);

        $expectedGeoJSON = <<<'EOF'
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
        EOF;

        self::assertSame($expectedGeoJSON, $geoJSONOutput);
    }

    public function testNestedGeometryCollection(): void
    {
        $writer = new GeoJSONWriter(prettyPrint: true, lenient: false);

        $geometry = GeometryCollection::of(
            GeometryCollection::of(
                Point::xy(12, 34),
            ),
        );

        $this->expectException(GeometryIOException::class);
        $this->expectExceptionMessage('GeoJSON does not allow nested GeometryCollections. You can allow this by setting the $lenient flag to true.');

        $writer->write($geometry);
    }

    public function testNestedGeometryCollectionInLenientMode(): void
    {
        $writer = new GeoJSONWriter(prettyPrint: true, lenient: true);

        $geometry = GeometryCollection::of(
            GeometryCollection::of(
                Point::xy(12, 34),
            ),
        );

        $geoJSONOutput = $writer->write($geometry);

        $expectedGeoJSON = <<<'EOF'
        {
            "type": "GeometryCollection",
            "geometries": [
                {
                    "type": "GeometryCollection",
                    "geometries": [
                        {
                            "type": "Point",
                            "coordinates": [
                                12,
                                34
                            ]
                        }
                    ]
                }
            ]
        }
        EOF;

        self::assertSame($expectedGeoJSON, $geoJSONOutput);
    }
}
