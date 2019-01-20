<?php

declare(strict_types = 1);

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;
use Brick\Geo\GeometryCollection;

/**
 * Builds geometries out of GeoJSON Text strings.
 */
class GeoJSONReader extends AbstractGeoJSONReader
{
    /**
     * @param string $geojson The GeoJSON to read.
     * @param int    $srid    The optional SRID of the geometry.
     *
     * @return GeometryCollection|Geometry
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\GeometryIOException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    public function read(string $geojson, int $srid = 0) : Geometry
    {
        $geojson_array = json_decode(strtoupper($geojson), true);

        $geometry = $this->readGeoJSON($geojson_array, $srid);

        return $geometry;
    }
}
