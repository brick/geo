<?php

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;

/**
 * Writes geometries in the Extended WKT format designed by PostGIS.
 */
class EWKTWriter extends WKTWriter
{
    /**
     * @param \Brick\Geo\Geometry $geometry
     *
     * @return string
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function write(Geometry $geometry)
    {
        return 'SRID=' . $geometry->SRID() . ';' . $this->prettyPrintSpace . $this->doWrite($geometry);
    }
}
