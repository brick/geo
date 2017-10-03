<?php

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;
use Brick\Geo\Exception\GeometryIOException;

/**
 * Builds geometries out of Well-Known Binary strings.
 */
class WKBReader extends AbstractWKBReader
{
    /**
     * @param string $wkb  The WKB to read.
     * @param int    $srid The optional SRID of the geometry.
     *
     * @return Geometry
     *
     * @throws GeometryIOException
     */
    public function read(string $wkb, $srid = 0) : Geometry
    {
        $buffer = new WKBBuffer($wkb);
        $geometry = $this->readGeometry($buffer, $srid);

        if (! $buffer->isEndOfStream()) {
            throw GeometryIOException::invalidWKB('unexpected data at end of stream');
        }

        return $geometry;
    }

    /**
     * {@inheritdoc}
     */
    protected function readGeometryHeader(WKBBuffer $buffer, & $geometryType, & $hasZ, & $hasM, & $srid) : void
    {
        $wkbType = $buffer->readUnsignedLong();

        $geometryType = $wkbType % 1000;
        $dimension = ($wkbType - $geometryType) / 1000;

        if ($dimension < 0 || $dimension > 3) {
            throw GeometryIOException::unsupportedWKBType($wkbType);
        }

        $hasZ = ($dimension === 1 || $dimension === 3);
        $hasM = ($dimension === 2 || $dimension === 3);
    }
}
