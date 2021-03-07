<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;

/**
 * Builds geometries out of Well-Known Text strings.
 */
class WKTReader extends AbstractWKTReader
{
    /**
     * @param string $wkt  The WKT to read.
     * @param int    $srid The optional SRID of the geometry.
     *
     * @throws GeometryIOException
     */
    public function read(string $wkt, int $srid = 0) : Geometry
    {
        $parser = new WKTParser(strtoupper($wkt));
        $geometry = $this->readGeometry($parser, $srid);

        if (! $parser->isEndOfStream()) {
            throw GeometryIOException::invalidWKT();
        }

        return $geometry;
    }
}
