<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;

/**
 * Writes geometries in the Extended WKB format designed by PostGIS.
 */
class EWKBWriter extends AbstractWKBWriter
{
    protected function packHeader(Geometry $geometry, bool $outer) : string
    {
        $geometryType = $geometry->geometryTypeBinary();

        $cs = $geometry->coordinateSystem();

        if ($cs->hasZ()) {
            $geometryType |= EWKBTools::Z;
        }

        if ($cs->hasM()) {
            $geometryType |= EWKBTools::M;
        }

        $srid = $cs->SRID();

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
