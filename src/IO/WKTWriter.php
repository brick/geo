<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;

/**
 * Converter class from Geometry to WKT.
 */
class WKTWriter extends AbstractWKTWriter
{
    public function write(Geometry $geometry) : string
    {
        return $this->doWrite($geometry);
    }
}
