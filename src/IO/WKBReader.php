<?php

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryParseException;

/**
 * Builds geometries out of Well-Known Binary strings.
 */
class WKBReader extends AbstractWKBReader
{
    /**
     * @param string  $wkb  The WKB to read.
     * @param integer $srid The optional SRID of the geometry.
     *
     * @return \Brick\Geo\Geometry
     *
     * @throws \Brick\Geo\Exception\GeometryParseException
     */
    public function read($wkb, $srid = 0)
    {
        $buffer = new WKBBuffer($wkb);
        $geometry = $this->readGeometry($buffer, $srid);

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
        $wkbType = $buffer->readUnsignedLong();

        $geometryType = $wkbType % 1000;
        $dimension = ($wkbType - $geometryType) / 1000;

        if ($dimension < 0 || $dimension > 3) {
            throw GeometryParseException::unsupportedWKBType($wkbType);
        }

        $hasZ = ($dimension === 1 || $dimension === 3);
        $hasM = ($dimension === 2 || $dimension === 3);
    }
}
