<?php

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;

/**
 * Writes geometries in the Extended WKB format designed by PostGIS.
 */
class EWKBWriter extends WKBWriter
{
    /**
     * {@inheritdoc}
     */
    protected function packHeader($geometryType, Geometry $geometry, $outer)
    {
        if ($geometry->is3D()) {
            $geometryType |= EWKBTools::Z;
        }

        if ($geometry->isMeasured()) {
            $geometryType |= EWKBTools::M;
        }

        $srid = $geometry->SRID();

        if ($srid !== 0 && $outer) {
            $geometryType |= EWKBTools::S;
        }

        $header = $this->packUnsignedInteger($geometryType);

        if ($srid !== 0 && $outer) {
            $header .= $this->packUnsignedInteger($srid);
        }

        return $header;
    }
}
