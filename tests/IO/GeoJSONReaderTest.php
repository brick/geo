<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\IO\GeoJSON\Feature;
use Brick\Geo\IO\GeoJSON\FeatureCollection;
use Brick\Geo\IO\GeoJSONReader;
use stdClass;

class GeoJSONReaderTest extends GeoJSONAbstractTestCase
{
    /**
     * @dataProvider providerReadGeometry
     *
     * @param string $geojson The GeoJSON to read.
     * @param array  $coords  The expected Geometry coordinates.
     * @param bool   $is3D    Whether the resulting Geometry has a Z coordinate.
     * @param bool   $lenient Whether to be lenient about case-sensitivity.
     */
    public function testReadGeometry(string $geojson, array $coords, bool $is3D, bool $lenient) : void
    {
        $geometry = (new GeoJSONReader($lenient))->read($geojson);
        $this->assertGeometryContents($geometry, $coords, $is3D, false, 4326);
    }

    public static function providerReadGeometry() : \Generator
    {
        foreach (self::providerGeometryGeoJSON() as [$geojson, $coords, $is3D]) {
            yield [$geojson, $coords, $is3D, false];
            yield [self::alterCase($geojson), $coords, $is3D, true];
        }
    }

    /**
     * @dataProvider providerReadFeature
     *
     * @param string        $geojson    The GeoJSON to read.
     * @param stdClass|null $properties The contained properties.
     * @param array|null    $coords     The expected Geometry coordinates, or null if the Feature has no geometry.
     * @param bool          $is3D       Whether the resulting Geometry has a Z coordinate.
     * @param bool          $lenient    Whether to be lenient about case-sensitivity.
     */
    public function testReadFeature(string $geojson, ?stdClass $properties, ?array $coords, bool $is3D, bool $lenient) : void
    {
        $feature = (new GeoJSONReader($lenient))->read($geojson);

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
        foreach (self::providerFeatureGeoJSON() as [$geojson, $properties, $coords, $is3D]) {
            yield [$geojson, $properties, $coords, $is3D, false];
            yield [self::alterCase($geojson), $properties, $coords, $is3D, true];
        }
    }

    /**
     * @dataProvider providerReadFeatureCollection
     *
     * @param string  $geojson The GeoJSON to read.
     * @param array[] $coords  The expected Point coordinates.
     * @param bool[]  $is3D    Whether the resulting Point has a Z coordinate.
     * @param bool    $lenient Whether to be lenient about case-sensitivity.
     */
    public function testReadFeatureCollection(string $geojson, array $properties, array $coords, array $is3D, bool $lenient) : void
    {
        $featureCollection = (new GeoJSONReader($lenient))->read($geojson);

        self::assertInstanceOf(FeatureCollection::class, $featureCollection);

        foreach ($featureCollection->getFeatures() as $key => $feature) {
            self::assertEquals($properties[$key], $feature->getProperties());
            $geometry = $feature->getGeometry();
            $this->assertGeometryContents($geometry, $coords[$key], $is3D[$key], false, 4326);
        }
    }

    public static function providerReadFeatureCollection() : \Generator
    {
        foreach (self::providerFeatureCollectionGeoJSON() as [$geojson, $properties, $coords, $is3D]) {
            yield [$geojson, $properties, $coords, $is3D, false];
            yield [self::alterCase($geojson), $properties, $coords, $is3D, true];
        }
    }

    /**
     * @dataProvider providerNonLenientReadWrongCaseType
     */
    public function testNonLenientReadWrongCaseType(string $geojson, string $expectedExceptionMessage) : void
    {
        $reader = new GeoJSONReader();

        $this->expectException(GeometryIOException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $reader->read($geojson);
    }

    public static function providerNonLenientReadWrongCaseType() : \Generator
    {
        foreach (self::providerGeometryPointGeoJSON() as [$geojson]) {
            yield [self::alterCase($geojson), 'Unsupported GeoJSON type: POINT.'];
        }

        foreach (self::providerFeaturePointGeoJSON() as [$geojson]) {
            yield [self::alterCase($geojson), 'Unsupported GeoJSON type: FEATURE.'];
        }

        foreach (self::providerFeatureCollectionGeoJSON() as [$geojson]) {
            yield [self::alterCase($geojson), 'Unsupported GeoJSON type: FEATURECOLLECTION.'];
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
