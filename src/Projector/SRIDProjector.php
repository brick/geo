<?php

declare(strict_types=1);

namespace Brick\Geo\Projector;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Point;

/**
 * Changes the SRID of a Geometry, without changing its coordinates.
 */
final class SRIDProjector implements Projector
{
    public function __construct(
        private readonly int $targetSRID,
    ) {
    }

    public function project(Point $point): Point
    {
        return new Point($point->coordinateSystem()->withSRID($this->targetSRID), ...$point->toArray());
    }

    public function getTargetCoordinateSystem(CoordinateSystem $sourceCoordinateSystem): CoordinateSystem
    {
        return $sourceCoordinateSystem->withSRID($this->targetSRID);
    }
}
