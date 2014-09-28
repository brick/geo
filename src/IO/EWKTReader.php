<?php

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryException;

/**
 * Reads geometries from the Extended WKT format designed by PostGIS.
 */
class EWKTReader extends WKTReader
{
    /**
     * @param string $ewkt The EWKT to read.
     *
     * @return \Brick\Geo\Geometry
     *
     * @throws GeometryException
     */
    public function read($ewkt)
    {
        $parser = new EWKTParser(strtoupper($ewkt));
        $srid = $parser->getOptionalSRID();
        $geometry = $this->readGeometry($parser, $srid);

        if (! $parser->isEndOfStream()) {
            throw GeometryException::invalidEWKT();
        }

        return $geometry;
    }
}
