<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\GeoJSONReader;

class GeoJSONReaderTest extends GeoJSONAbstractTest
{
    /**
     * @dataProvider providerRead
     *
     * @param string $geojson    The GeoJSON to read.
     * @param array  $coords     The expected Point coordinates.
     * @param bool   $is3D       Whether the resulting Point has a Z coordinate.
     * @param bool   $isMeasured Whether the resulting Point has a M coordinate.
     * @param int    $srid       The SRID to use.
     *
     * @return void
     * @throws \Brick\Geo\Exception\GeometryIOException
     */
    public function testRead(string $geojson, array $coords, bool $is3D, bool $isMeasured, int $srid) : void
    {
        $geometry = (new GeoJSONReader())->read($geojson, $srid);
        $this->assertGeometryContents($geometry, $coords, $is3D, $isMeasured, $srid);
    }

    /**
     * @return \Generator
     */
    public function providerRead() : \Generator
    {
        foreach ($this->providerGeoJSON() as [$geojson, $coords, $is3D, $isMeasured]) {
            yield [$geojson, $coords, $is3D, $isMeasured, 0];
            yield [$this->alter($geojson), $coords, $is3D, $isMeasured, 4326];
        }
    }

    /**
     * Adds extra spaces to a GeoJSON string, and changes its case.
     *
     * The result is still a valid GeoJSON string, that the reader should be able to handle.
     *
     * @param string $geojson
     *
     * @return string
     */
    private function alter(string $geojson) : string
    {
        $search = [' ', '{', '}', ','];
        $replace = [];

        foreach ($search as $char) {
            $replace[] = " $char ";
        }

        $geojson = str_replace($search, $replace, $geojson);

        return strtolower(" $geojson ");
    }
}