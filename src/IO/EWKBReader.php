<?php

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;
use Brick\Geo\Exception\GeometryParseException;

/**
 * Reads geometries out of the Extended WKB format designed by PostGIS.
 */
class EWKBReader extends WKBAbstractReader
{
    /**
     * @param string $ewkb The EWKB to read.
     *
     * @return Geometry
     *
     * @throws GeometryParseException
     */
    public function read($ewkb)
    {
        $buffer = new WKBBuffer($ewkb);
        $geometry = $this->readGeometry($buffer, 0);

        if (! $buffer->isEndOfStream()) {
            throw GeometryParseException::invalidWKB('unexpected data at end of stream');
        }

        return $geometry;
    }

    /**
     * {@inheritdoc}
     */
    protected function readGeometryHeader(WKBBuffer $buffer, & $geometryType, & $hasZ, & $hasM, & $srid)
    {
        $header = $buffer->readUnsignedLong();

        if ($header >= 0 && $header < 4000) {
            $geometryType = $header % 1000;
            $dimension = ($header - $geometryType) / 1000;

            if ($dimension < 0 || $dimension > 3) {
                throw GeometryParseException::unsupportedWKBType($header);
            }

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
    }
}
