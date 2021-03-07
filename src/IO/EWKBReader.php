<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;
use Brick\Geo\Exception\GeometryIOException;

/**
 * Reads geometries out of the Extended WKB format designed by PostGIS.
 */
class EWKBReader extends AbstractWKBReader
{
    /**
     * @throws GeometryIOException
     */
    public function read(string $ewkb) : Geometry
    {
        $buffer = new WKBBuffer($ewkb);
        $geometry = $this->readGeometry($buffer, 0);

        if (! $buffer->isEndOfStream()) {
            throw GeometryIOException::invalidWKB('unexpected data at end of stream');
        }

        return $geometry;
    }

    protected function readGeometryHeader(WKBBuffer $buffer) : WKBGeometryHeader
    {
        $header = $buffer->readUnsignedLong();

        $srid = null;

        if ($header >= 0 && $header < 4000) {
            $geometryType = $header % 1000;
            $dimension = ($header - $geometryType) / 1000;

            $hasZ = ($dimension === 1 || $dimension === 3);
            $hasM = ($dimension === 2 || $dimension === 3);
        } else {
            $geometryType = $header & 0xFFF;

            $hasZ    = (($header & EWKBTools::Z) !== 0);
            $hasM    = (($header & EWKBTools::M) !== 0);
            $hasSRID = (($header & EWKBTools::S) !== 0);

            if ($hasSRID) {
                $srid = $buffer->readUnsignedLong();
            }
        }

        return new WKBGeometryHeader($geometryType, $hasZ, $hasM, $srid);
    }
}
