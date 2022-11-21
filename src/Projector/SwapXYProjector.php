<?php

declare(strict_types=1);

namespace Brick\Geo\Projector;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Point;

/**
 * Swaps the X and Y coordinates of a Geometry, while keeping the same SRID.
 */
final class SwapXYProjector implements Projector
{
    public function project(Point $point): Point
    {
        if ($point->isEmpty()) {
            return $point;
        }

        $coordinates = $point->toArray();

        [$x, $y] = $coordinates;

        $coordinates[0] = $y;
        $coordinates[1] = $x;

        return new Point($point->coordinateSystem(), ...$coordinates);
    }

    public function getTargetCoordinateSystem(CoordinateSystem $sourceCoordinateSystem): CoordinateSystem
    {
        return $sourceCoordinateSystem;
    }
}
