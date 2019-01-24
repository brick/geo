<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\GeometryCollection;
use Brick\Geo\IO\GeoJSONReader;

class GeoJSONReaderTest extends GeoJSONAbstractTest
{
    /**
     * @dataProvider providerReadGeometry
     *
     * @param string $geojson The GeoJSON to read.
     * @param array  $coords  The expected Geometry coordinates.
     * @param bool   $is3D    Whether the resulting Geometry has a Z coordinate.
     *
     * @return void
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function testReadGeometry(string $geojson, array $coords, bool $is3D): void
    {
        $geometry = (new GeoJSONReader())->read($geojson);
        $this->assertGeometryContents($geometry, $coords, $is3D, false, 4326);
    }

    /**
     * @return \Generator
     */
    public function providerReadGeometry(): \Generator
    {
        foreach ($this->providerGeometryGeoJSON() as [$geojson, $coords, $is3D]) {
            yield [$geojson, $coords, $is3D];
            yield [$this->alter($geojson), $coords, $is3D];
        }
    }

    /**
     * @dataProvider providerReadFeature
     *
     * @param string $geojson The GeoJSON to read.
     * @param array  $coords  The expected Geometry coordinates.
     * @param bool   $is3D    Whether the resulting Geometry has a Z coordinate.
     *
     * @return void
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function testReadFeature(string $geojson, array $coords, bool $is3D): void
    {
        $geometry = (new GeoJSONReader())->read($geojson);
        $this->assertGeometryContents($geometry, $coords, $is3D, false, 4326);
    }

    /**
     * @return \Generator
     */
    public function providerReadFeature(): \Generator
    {
        foreach ($this->providerFeatureGeoJSON() as [$geojson, $coords, $is3D]) {
            yield [$geojson, $coords, $is3D];
            yield [$this->alter($geojson), $coords, $is3D];
        }
    }

    /**
     * @dataProvider providerReadFeatureCollection
     *
     * @param string  $geojson The GeoJSON to read.
     * @param array[] $coords  The expected Point coordinates.
     * @param bool[]  $is3D    Whether the resulting Point has a Z coordinate.
     *
     * @return void
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function testReadFeatureCollection(
        string $geojson,
        array $coords,
        array $is3D
    ): void {
        $geometryCollection = (new GeoJSONReader())->read($geojson);

        $this->assertInstanceOf(GeometryCollection::class, $geometryCollection);

        foreach ($geometryCollection->geometries() as $key => $geometry) {
            $this->assertGeometryContents($geometry, $coords[$key], $is3D[$key], false, 4326);
        }
    }

    /**
     * @return \Generator
     */
    public function providerReadFeatureCollection(): \Generator
    {
        foreach ($this->providerFeatureCollectionGeoJSON() as [$geojson, $coords, $is3D]) {
            yield [$geojson, $coords, $is3D];
            yield [$this->alter($geojson), $coords, $is3D];
        }
    }

    /**
     * Adds extra spaces to a GeoJSON string.
     *
     * The result is still a valid GeoJSON string, that the reader should be able to handle.
     *
     * @param string $geojson
     *
     * @return string
     */
    private function alter(string $geojson): string
    {
        $search = [' ', '{', '}', ','];
        $replace = [];

        foreach ($search as $char) {
            $replace[] = " $char ";
        }

        $geojson = str_replace($search, $replace, $geojson);

        return $geojson;
    }
}