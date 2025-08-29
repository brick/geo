<?php

declare(strict_types=1);

namespace Brick\Geo\Io;

use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\Geometry;
use Brick\Geo\Io\Internal\AbstractWktReader;
use Brick\Geo\Io\Internal\WktParser;

use function strtoupper;

/**
 * Reads geometries from the Extended WKT format designed by PostGIS.
 */
final class EwktReader extends AbstractWktReader
{
    /**
     * @throws GeometryIoException
     */
    public function read(string $ewkt): Geometry
    {
        $parser = new WktParser(strtoupper($ewkt), true);
        $srid = $parser->getOptionalSrid();
        $geometry = $this->readGeometry($parser, $srid);

        if (! $parser->isEndOfStream()) {
            throw GeometryIoException::invalidEwkt();
        }

        return $geometry;
    }
}
