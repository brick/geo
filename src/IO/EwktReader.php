<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;
use Brick\Geo\IO\Internal\AbstractWktReader;
use Brick\Geo\IO\Internal\WktParser;

/**
 * Reads geometries from the Extended WKT format designed by PostGIS.
 */
final class EwktReader extends AbstractWktReader
{
    /**
     * @throws GeometryIOException
     */
    public function read(string $ewkt) : Geometry
    {
        $parser = new WktParser(strtoupper($ewkt), true);
        $srid = $parser->getOptionalSRID();
        $geometry = $this->readGeometry($parser, $srid);

        if (! $parser->isEndOfStream()) {
            throw GeometryIOException::invalidEwkt();
        }

        return $geometry;
    }
}
