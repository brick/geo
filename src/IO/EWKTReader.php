<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;
use Brick\Geo\IO\Internal\AbstractWKTReader;
use Brick\Geo\IO\Internal\WKTParser;

/**
 * Reads geometries from the Extended WKT format designed by PostGIS.
 */
final class EWKTReader extends AbstractWKTReader
{
    /**
     * @throws GeometryIOException
     */
    public function read(string $ewkt) : Geometry
    {
        $parser = new WKTParser(strtoupper($ewkt), true);
        $srid = $parser->getOptionalSRID();
        $geometry = $this->readGeometry($parser, $srid);

        if (! $parser->isEndOfStream()) {
            throw GeometryIOException::invalidEWKT();
        }

        return $geometry;
    }
}
