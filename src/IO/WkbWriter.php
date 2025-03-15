<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;
use Brick\Geo\IO\Internal\AbstractWkbWriter;
use Override;

/**
 * Writes geometries in the WKB format.
 */
final class WkbWriter extends AbstractWkbWriter
{
    #[Override]
    protected function packHeader(Geometry $geometry, bool $outer) : string
    {
        $geometryType = $geometry->geometryTypeBinary();

        $cs = $geometry->coordinateSystem();

        if ($cs->hasZ()) {
            $geometryType += 1000;
        }

        if ($cs->hasM()) {
            $geometryType += 2000;
        }

        return $this->packUnsignedInteger($geometryType);
    }
}
