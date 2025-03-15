<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;
use Brick\Geo\IO\Internal\AbstractWktWriter;
use Override;

/**
 * Converter class from Geometry to WKT.
 */
final class WktWriter extends AbstractWktWriter
{
    #[Override]
    public function write(Geometry $geometry) : string
    {
        return $this->doWrite($geometry);
    }
}
