<?php

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;
use Brick\Geo\Exception\GeometryIOException;

/**
 * Reads geometries from the Extended WKT format designed by PostGIS.
 */
class EWKTReader extends AbstractWKTReader
{
    /**
     * @param string $ewkt The EWKT to read.
     *
     * @return Geometry
     *
     * @throws GeometryIOException
     */
    public function read($ewkt)
    {
        $parser = new EWKTParser(strtoupper($ewkt));
        $srid = $parser->getOptionalSRID();
        $geometry = $this->readGeometry($parser, $srid);

        if (! $parser->isEndOfStream()) {
            throw GeometryIOException::invalidEWKT();
        }

        return $geometry;
    }
}
