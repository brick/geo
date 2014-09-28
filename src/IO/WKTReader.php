<?php

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;
use Brick\Geo\Exception\GeometryException;

/**
 * Builds geometries out of Well-Known Text strings.
 */
class WKTReader extends WKTAbstractReader
{
    /**
     * @param string  $wkt  The WKT to read.
     * @param integer $srid The optional SRID of the geometry.
     *
     * @return Geometry
     *
     * @throws GeometryException
     */
    public function read($wkt, $srid = 0)
    {
        $parser = new WKTParser(strtoupper($wkt));
        $geometry = $this->readGeometry($parser, $srid);

        if (! $parser->isEndOfStream()) {
            throw GeometryException::invalidWkt();
        }

        return $geometry;
    }
}
