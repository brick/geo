<?php

declare(strict_types=1);

namespace Brick\Geo\Projector;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Point;
use Override;

/**
 * Changes the SRID of a Geometry, without changing its coordinates.
 */
final class SridProjector implements Projector
{
    public function __construct(
        private readonly int $targetSRID,
    ) {
    }

    #[Override]
    public function project(Point $point): Point
    {
        return new Point($point->coordinateSystem()->withSRID($this->targetSRID), ...$point->toArray());
    }

    #[Override]
    public function getTargetCoordinateSystem(CoordinateSystem $sourceCoordinateSystem): CoordinateSystem
    {
        return $sourceCoordinateSystem->withSRID($this->targetSRID);
    }
}
