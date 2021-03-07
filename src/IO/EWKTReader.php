<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;
use Brick\Geo\Exception\GeometryIOException;

/**
 * Reads geometries from the Extended WKT format designed by PostGIS.
 */
class EWKTReader extends AbstractWKTReader
{
    /**
     * @throws GeometryIOException
     */
    public function read(string $ewkt) : Geometry
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
