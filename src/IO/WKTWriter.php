<?php

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;

/**
 * Converter class from Geometry to WKT.
 */
class WKTWriter extends AbstractWKTWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(Geometry $geometry)
    {
        return $this->doWrite($geometry);
    }
}
