<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;
use Brick\Geo\IO\Internal\AbstractWktReader;
use Brick\Geo\IO\Internal\WktParser;

/**
 * Builds geometries out of Well-Known Text strings.
 */
final class WktReader extends AbstractWktReader
{
    /**
     * @param string $wkt  The WKT to read.
     * @param int    $srid The optional SRID of the geometry.
     *
     * @throws GeometryIOException
     */
    public function read(string $wkt, int $srid = 0) : Geometry
    {
        $parser = new WktParser(strtoupper($wkt), false);
        $geometry = $this->readGeometry($parser, $srid);

        if (! $parser->isEndOfStream()) {
            throw GeometryIOException::invalidWkt();
        }

        return $geometry;
    }
}
