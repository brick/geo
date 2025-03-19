<?php

declare(strict_types=1);

namespace Brick\Geo\Io;

use Brick\Geo\Geometry;
use Brick\Geo\Io\Internal\AbstractWkbWriter;
use Brick\Geo\Io\Internal\WkbTools;
use Override;

/**
 * Writes geometries in the Extended WKB format designed by PostGIS.
 */
final readonly class EwkbWriter extends AbstractWkbWriter
{
    #[Override]
    protected function packHeader(Geometry $geometry, bool $outer) : string
    {
        $geometryType = $geometry->geometryTypeBinary();

        $cs = $geometry->coordinateSystem();

        if ($cs->hasZ()) {
            $geometryType |= WkbTools::Z;
        }

        if ($cs->hasM()) {
            $geometryType |= WkbTools::M;
        }

        $srid = $cs->srid();

        if ($srid !== 0 && $outer) {
            $geometryType |= WkbTools::S;
        }

        $header = $this->packUnsignedInteger($geometryType);

        if ($srid !== 0 && $outer) {
            $header .= $this->packUnsignedInteger($srid);
        }

        return $header;
    }
}
