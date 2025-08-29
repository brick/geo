<?php

declare(strict_types=1);

namespace Brick\Geo\Io;

use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\Geometry;
use Brick\Geo\Io\Internal\AbstractWktReader;
use Brick\Geo\Io\Internal\WktParser;

use function strtoupper;

/**
 * Builds geometries out of Well-Known Text strings.
 */
final class WktReader extends AbstractWktReader
{
    /**
     * @param string $wkt  The WKT to read.
     * @param int    $srid The optional SRID of the geometry.
     *
     * @throws GeometryIoException
     */
    public function read(string $wkt, int $srid = 0): Geometry
    {
        $parser = new WktParser(strtoupper($wkt), false);
        $geometry = $this->readGeometry($parser, $srid);

        if (! $parser->isEndOfStream()) {
            throw GeometryIoException::invalidWkt();
        }

        return $geometry;
    }
}
