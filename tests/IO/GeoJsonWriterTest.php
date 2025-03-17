<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\GeometryCollection;
use Brick\Geo\Io\GeoJsonReader;
use Brick\Geo\Io\GeoJsonWriter;
use Brick\Geo\Point;
use Brick\Geo\Polygon;
use PHPUnit\Framework\Attributes\DataProvider;

class GeoJsonWriterTest extends GeoJsonAbstractTestCase
{
    #[DataProvider('providerGeometryGeoJson')]
    #[DataProvider('providerFeatureGeoJson')]
    #[DataProvider('providerFeatureCollectionGeoJson')]
    public function testWriteGeometry(string $geoJson) : void
    {
        $geometry = (new GeoJsonReader())->read($geoJson);
        $geometryGeoJson = (new GeoJsonWriter())->write($geometry);

        self::assertSame($geoJson, $geometryGeoJson);
    }

    public function testPrettyPrint() : void
    {
        $writer = new GeoJsonWriter(prettyPrint: true);
        $geoJsonOutput = $writer->write(Point::xyz(1, 2, 3));

        $expectedGeoJson = <<<'EOF'
        {
            "type": "Point",
            "coordinates": [
                1,
                2,
                3
            ]
        }
        EOF;

        self::assertSame($expectedGeoJson, $geoJsonOutput);
    }

    public function testWriteGeometryWithM() : void
    {
        $writer = new GeoJsonWriter(prettyPrint: true);

        // the M coordinate must be ignored
        $geoJsonOutput = $writer->write(Point::xym(1, 2, 3));

        $expectedGeoJson = <<<'EOF'
        {
            "type": "Point",
            "coordinates": [
                1,
                2
            ]
        }
        EOF;

        self::assertSame($expectedGeoJson, $geoJsonOutput);
    }

    public function testWriteGeometryWithBbox() : void
    {
        $writer = new GeoJsonWriter(prettyPrint: true, setBbox: true);

        $polygon = Polygon::fromText('POLYGON((2 2, 1 5, 2 8, 3 5, 2 2))');
        $geoJsonOutput = $writer->write($polygon);

        $expectedGeoJson = <<<'EOF'
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

        self::assertSame($expectedGeoJson, $geoJsonOutput);
    }

    public function testNestedGeometryCollection(): void
    {
        $writer = new GeoJsonWriter(prettyPrint: true, lenient: false);

        $geometry = GeometryCollection::of(
            GeometryCollection::of(
                Point::xy(12, 34),
            ),
        );

        $this->expectException(GeometryIoException::class);
        $this->expectExceptionMessage('GeoJSON does not allow nested GeometryCollections. You can allow this by setting the $lenient flag to true.');

        $writer->write($geometry);
    }

    public function testNestedGeometryCollectionInLenientMode(): void
    {
        $writer = new GeoJsonWriter(prettyPrint: true, lenient: true);

        $geometry = GeometryCollection::of(
            GeometryCollection::of(
                Point::xy(12, 34),
            ),
        );

        $geoJsonOutput = $writer->write($geometry);

        $expectedGeoJson = <<<'EOF'
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

        self::assertSame($expectedGeoJson, $geoJsonOutput);
    }
}
