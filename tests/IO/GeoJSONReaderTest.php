<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\IO\GeoJSON\Feature;
use Brick\Geo\IO\GeoJSON\FeatureCollection;
use Brick\Geo\IO\GeoJSONReader;
use stdClass;

class GeoJSONReaderTest extends GeoJSONAbstractTest
{
    /**
     * @dataProvider providerReadGeometry
     *
     * @param string $geojson The GeoJSON to read.
     * @param array  $coords  The expected Geometry coordinates.
     * @param bool   $is3D    Whether the resulting Geometry has a Z coordinate.
     * @param bool   $lenient Whether to be lenient about case-sensitivity.
     *
     * @return void
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function testReadGeometry(string $geojson, array $coords, bool $is3D, bool $lenient) : void
    {
        $geometry = (new GeoJSONReader($lenient))->read($geojson);
        $this->assertGeometryContents($geometry, $coords, $is3D, false, 4326);
    }

    /**
     * @return \Generator
     */
    public function providerReadGeometry() : \Generator
    {
        foreach ($this->providerGeometryGeoJSON() as [$geojson, $coords, $is3D]) {
            yield [$geojson, $coords, $is3D, false];
            yield [$this->alterCase($geojson), $coords, $is3D, true];
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
     *
     * @return void
     *
     * @throws \Brick\Geo\Exception\GeometryException
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

    /**
     * @return \Generator
     */
    public function providerReadFeature() : \Generator
    {
        foreach ($this->providerFeatureGeoJSON() as [$geojson, $properties, $coords, $is3D]) {
            yield [$geojson, $properties, $coords, $is3D, false];
            yield [$this->alterCase($geojson), $properties, $coords, $is3D, true];
        }
    }

    /**
     * @dataProvider providerReadFeatureCollection
     *
     * @param string  $geojson The GeoJSON to read.
     * @param array[] $coords  The expected Point coordinates.
     * @param bool[]  $is3D    Whether the resulting Point has a Z coordinate.
     * @param bool    $lenient Whether to be lenient about case-sensitivity.
     *
     * @return void
     *
     * @throws \Brick\Geo\Exception\GeometryException
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

    /**
     * @return \Generator
     */
    public function providerReadFeatureCollection() : \Generator
    {
        foreach ($this->providerFeatureCollectionGeoJSON() as [$geojson, $properties, $coords, $is3D]) {
            yield [$geojson, $properties, $coords, $is3D, false];
            yield [$this->alterCase($geojson), $properties, $coords, $is3D, true];
        }
    }

    /**
     * @dataProvider providerNonLenientReadWrongCaseType
     *
     * @param string $geojson
     * @param string $expectedExceptionMessage
     *
     * @return void
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function testNonLenientReadWrongCaseType(string $geojson, string $expectedExceptionMessage) : void
    {
        $reader = new GeoJSONReader();

        $this->expectException(GeometryIOException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $reader->read($geojson);
    }

    /**
     * @return \Generator
     */
    public function providerNonLenientReadWrongCaseType() : \Generator
    {
        foreach ($this->providerGeometryPointGeoJSON() as [$geojson]) {
            yield [$this->alterCase($geojson), 'Unsupported GeoJSON type: POINT.'];
        }

        foreach ($this->providerFeaturePointGeoJSON() as [$geojson]) {
            yield [$this->alterCase($geojson), 'Unsupported GeoJSON type: FEATURE.'];
        }

        foreach ($this->providerFeatureCollectionGeoJSON() as [$geojson]) {
            yield [$this->alterCase($geojson), 'Unsupported GeoJSON type: FEATURECOLLECTION.'];
        }
    }

    /**
     * Changes the case of type attributes.
     *
     * @param string $geojson
     *
     * @return string
     */
    private function alterCase(string $geojson) : string
    {
        $callback = function(array $matches) : string {
            return $matches[1] . strtoupper($matches[2]);
        };

        return preg_replace_callback('/("type"\s*\:\s*)("[^"]+")/', $callback, $geojson);
    }
}
