<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\GeometryCollection;
use Brick\Geo\Io\GeoJson\Feature;
use Brick\Geo\Io\GeoJson\FeatureCollection;
use Brick\Geo\Io\GeoJsonReader;
use Brick\Geo\Point;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

class GeoJsonReaderTest extends GeoJsonAbstractTestCase
{
    /**
     * @param string $geojson The GeoJSON to read.
     * @param array  $coords  The expected Geometry coordinates.
     * @param bool   $is3D    Whether the resulting Geometry has a Z coordinate.
     * @param bool   $lenient Whether to be lenient about case-sensitivity.
     */
    #[DataProvider('providerReadGeometry')]
    public function testReadGeometry(string $geojson, array $coords, bool $is3D, bool $lenient) : void
    {
        $geometry = (new GeoJsonReader($lenient))->read($geojson);
        $this->assertGeometryContents($geometry, $coords, $is3D, false, 4326);
    }

    public static function providerReadGeometry() : \Generator
    {
        foreach (self::providerGeometryGeoJson() as [$geojson, $coords, $is3D]) {
            yield [$geojson, $coords, $is3D, false];
            yield [self::alterCase($geojson), $coords, $is3D, true];
        }
    }

    /**
     * @param string        $geojson    The GeoJSON to read.
     * @param stdClass|null $properties The contained properties.
     * @param array|null    $coords     The expected Geometry coordinates, or null if the Feature has no geometry.
     * @param bool          $is3D       Whether the resulting Geometry has a Z coordinate.
     * @param bool          $lenient    Whether to be lenient about case-sensitivity.
     */
    #[DataProvider('providerReadFeature')]
    public function testReadFeature(string $geojson, ?stdClass $properties, ?array $coords, bool $is3D, bool $lenient) : void
    {
        $feature = (new GeoJsonReader($lenient))->read($geojson);

        self::assertInstanceOf(Feature::class, $feature);
        self::assertEquals($properties, $feature->getProperties());

        $geometry = $feature->getGeometry();

        if ($coords === null) {
            self::assertNull($geometry);
        } else {
            $this->assertGeometryContents($geometry, $coords, $is3D, false, 4326);
        }
    }

    public static function providerReadFeature() : \Generator
    {
        foreach (self::providerFeatureGeoJson() as [$geojson, $properties, $coords, $is3D]) {
            yield [$geojson, $properties, $coords, $is3D, false];
            yield [self::alterCase($geojson), $properties, $coords, $is3D, true];
        }
    }

    /**
     * @param string  $geojson The GeoJSON to read.
     * @param array[] $coords  The expected Point coordinates.
     * @param bool[]  $is3D    Whether the resulting Point has a Z coordinate.
     * @param bool    $lenient Whether to be lenient about case-sensitivity.
     */
    #[DataProvider('providerReadFeatureCollection')]
    public function testReadFeatureCollection(string $geojson, array $properties, array $coords, array $is3D, bool $lenient) : void
    {
        $featureCollection = (new GeoJsonReader($lenient))->read($geojson);

        self::assertInstanceOf(FeatureCollection::class, $featureCollection);

        foreach ($featureCollection->getFeatures() as $key => $feature) {
            self::assertEquals($properties[$key], $feature->getProperties());
            $geometry = $feature->getGeometry();
            $this->assertGeometryContents($geometry, $coords[$key], $is3D[$key], false, 4326);
        }
    }

    public static function providerReadFeatureCollection() : \Generator
    {
        foreach (self::providerFeatureCollectionGeoJson() as [$geojson, $properties, $coords, $is3D]) {
            yield [$geojson, $properties, $coords, $is3D, false];
            yield [self::alterCase($geojson), $properties, $coords, $is3D, true];
        }
    }

    public function testReadFeatureWithMissingGeometry() : void
    {
        $reader = new GeoJsonReader();

        $geoJSON = <<<'EOF'
        {
            "type": "Feature",
            "properties": {
                "name": "Foo"
            }
        }
        EOF;

        $this->expectException(GeometryIoException::class);
        $this->expectExceptionMessage(
            'Missing "Feature.geometry" attribute. Features without geometry should use an explicit null value for ' .
            'this field. You can ignore this error by setting the $lenient flag to true.',
        );

        $reader->read($geoJSON);
    }

    public function testReadFeatureWithMissingGeometryInLenientMode() : void
    {
        $reader = new GeoJsonReader(lenient: true);

        $geoJSON = <<<'EOF'
        {
            "type": "Feature",
            "properties": {
                "name": "Foo"
            }
        }
        EOF;

        $feature = $reader->read($geoJSON);

        self::assertInstanceOf(Feature::class, $feature);
        self::assertNull($feature->getGeometry());
        self::assertNotNull($feature->getProperties());
        self::assertSame(['name' => 'Foo'], (array) $feature->getProperties());
    }

    public function testReadFeatureWithMissingProperties() : void
    {
        $reader = new GeoJsonReader();

        $geoJSON = <<<'EOF'
        {
            "type": "Feature",
            "geometry": {
                "type": "Point",
                "coordinates": [1, 2]
            }
        }
        EOF;

        $this->expectException(GeometryIoException::class);
        $this->expectExceptionMessage(
            'Missing "Feature.properties" attribute. Features without properties should use an explicit null value for ' .
            'this field. You can ignore this error by setting the $lenient flag to true.',
        );

        $reader->read($geoJSON);
    }

    public function testReadFeatureWithMissingPropertiesInLenientMode() : void
    {
        $reader = new GeoJsonReader(lenient: true);

        $geoJSON = <<<'EOF'
        {
            "type": "Feature",
            "geometry": {
                "type": "Point",
                "coordinates": [1, 2]
            }
        }
        EOF;

        $feature = $reader->read($geoJSON);

        self::assertInstanceOf(Feature::class, $feature);
        self::assertNull($feature->getProperties());
        self::assertInstanceOf(Point::class, $feature->getGeometry());
        self::assertSame([1.0, 2.0], $feature->getGeometry()->toArray());
    }

    public function testNestedGeometryCollection(): void
    {
        $reader = new GeoJsonReader();

        $geoJSON = <<<'EOF'
        {
            "type": "GeometryCollection",
            "geometries": [
                {
                    "type": "GeometryCollection",
                    "geometries": [
                        {
                            "type": "Point",
                            "coordinates": [12, 34]
                        }
                    ]
                }
            ]
        }
        EOF;

        $this->expectException(GeometryIoException::class);
        $this->expectExceptionMessage(
            'Invalid GeoJSON: GeoJSON does not allow nested GeometryCollections. You can allow this by setting the ' .
            '$lenient flag to true.',
        );

        $reader->read($geoJSON);
    }

    public function testNestedGeometryCollectionInLenientMode(): void
    {
        $reader = new GeoJsonReader(lenient: true);

        $geoJSON = <<<'EOF'
        {
            "type": "GeometryCollection",
            "geometries": [
                {
                    "type": "GeometryCollection",
                    "geometries": [
                        {
                            "type": "Point",
                            "coordinates": [12, 34]
                        }
                    ]
                }
            ]
        }
        EOF;

        $geometryCollection = $reader->read($geoJSON);

        self::assertInstanceOf(GeometryCollection::class, $geometryCollection);
        $geometries = $geometryCollection->geometries();
        self::assertCount(1, $geometries);
        self::assertInstanceOf(GeometryCollection::class, $geometries[0]);
        $subGeometries = $geometries[0]->geometries();
        self::assertCount(1, $subGeometries);
        self::assertInstanceOf(Point::class, $subGeometries[0]);
        self::assertSame([12.0, 34.0], $subGeometries[0]->toArray());
    }

    #[DataProvider('providerWrongCaseTypeInNonLenientMode')]
    public function testWrongCaseTypeInNonLenientMode(string $geojson, string $expectedExceptionMessage) : void
    {
        $reader = new GeoJsonReader();

        $this->expectException(GeometryIoException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $reader->read($geojson);
    }

    public static function providerWrongCaseTypeInNonLenientMode() : \Generator
    {
        $tests = [
            [self::providerGeometryPointGeoJson(), 'POINT', 'Point'],
            [self::providerGeometryLineStringGeoJson(), 'LINESTRING', 'LineString'],
            [self::providerGeometryPolygonGeoJson(), 'POLYGON', 'Polygon'],
            [self::providerGeometryMultiPointGeoJson(), 'MULTIPOINT', 'MultiPoint'],
            [self::providerGeometryMultiLineStringGeoJson(), 'MULTILINESTRING', 'MultiLineString'],
            [self::providerGeometryMultiPolygonGeoJson(), 'MULTIPOLYGON', 'MultiPolygon'],
            [self::providerGeometryCollectionGeoJson(), 'GEOMETRYCOLLECTION', 'GeometryCollection'],
            [self::providerFeaturePointGeoJson(), 'FEATURE', 'Feature'],
            [self::providerFeatureCollectionGeoJson(), 'FEATURECOLLECTION', 'FeatureCollection'],
        ];

        foreach ($tests as [$provider, $wrongCase, $correctCase]) {
            foreach ($provider as [$geoJSON]) {
                yield [
                    self::alterCase($geoJSON),
                    "Unsupported GeoJSON type: $wrongCase. The correct case is $correctCase. You can allow incorrect cases by setting the \$lenient flag to true.",
                ];
            }
        }
    }

    /**
     * Changes the case of type attributes.
     */
    private static function alterCase(string $geojson) : string
    {
        $callback = fn(array $matches): string => $matches[1] . strtoupper($matches[2]);

        return preg_replace_callback('/("type"\s*\:\s*)("[^"]+")/', $callback, $geojson);
    }
}
