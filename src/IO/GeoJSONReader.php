<?php

declare(strict_types = 1);

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;

/**
 * Builds geometries out of GeoJSON Text strings.
 */
class GeoJSONReader extends AbstractGeoJSONReader
{
    /**
     * @param string $geojson The GeoJSON to read.
     * @param int    $srid    The optional SRID of the geometry.
     *
     * @return Geometry
     *
     * @throws GeometryIOException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     */
    public function read(string $geojson, int $srid = 0) : Geometry
    {
        $parser = new GeoJSONParser(strtoupper($geojson));
        $geometry = $this->readGeometry($parser, $srid);

        return $geometry;
    }
}
