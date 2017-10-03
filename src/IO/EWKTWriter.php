<?php

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;

/**
 * Writes geometries in the Extended WKT format designed by PostGIS.
 */
class EWKTWriter extends AbstractWKTWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(Geometry $geometry) : string
    {
        $srid = $geometry->SRID();

        if ($srid === 0) {
            return $this->doWrite($geometry);
        }

        return 'SRID=' . $geometry->SRID() . ';' . $this->prettyPrintSpace . $this->doWrite($geometry);
    }
}
