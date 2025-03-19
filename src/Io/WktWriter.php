<?php

declare(strict_types=1);

namespace Brick\Geo\Io;

use Brick\Geo\Geometry;
use Brick\Geo\Io\Internal\AbstractWktWriter;
use Override;

/**
 * Converter class from Geometry to WKT.
 */
final readonly class WktWriter extends AbstractWktWriter
{
    #[Override]
    public function write(Geometry $geometry) : string
    {
        return $this->doWrite($geometry);
    }
}
