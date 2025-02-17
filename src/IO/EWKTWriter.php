<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;
use Override;

/**
 * Writes geometries in the Extended WKT format designed by PostGIS.
 */
final class EWKTWriter extends AbstractWKTWriter
{
    #[Override]
    public function write(Geometry $geometry) : string
    {
        $srid = $geometry->SRID();

        if ($srid === 0) {
            return $this->doWrite($geometry);
        }

        return 'SRID=' . $geometry->SRID() . ';' . $this->prettyPrintSpace . $this->doWrite($geometry);
    }
}
