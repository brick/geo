<?php

declare(strict_types=1);

namespace Brick\Geo\Io;

use Brick\Geo\Geometry;
use Brick\Geo\Io\Internal\AbstractWktWriter;
use Override;

/**
 * Writes geometries in the Extended WKT format designed by PostGIS.
 */
final class EwktWriter extends AbstractWktWriter
{
    #[Override]
    public function write(Geometry $geometry) : string
    {
        $srid = $geometry->srid();

        if ($srid === 0) {
            return $this->doWrite($geometry);
        }

        return 'SRID=' . $geometry->srid() . ';' . $this->prettyPrintSpace . $this->doWrite($geometry);
    }
}
