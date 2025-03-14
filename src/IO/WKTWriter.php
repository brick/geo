<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;
use Brick\Geo\IO\Internal\AbstractWKTWriter;
use Override;

/**
 * Converter class from Geometry to WKT.
 */
final class WKTWriter extends AbstractWKTWriter
{
    #[Override]
    public function write(Geometry $geometry) : string
    {
        return $this->doWrite($geometry);
    }
}
