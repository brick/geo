<?php

declare(strict_types=1);

namespace Brick\Geo\Projector;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Point;
use Override;

/**
 * Swaps the X and Y coordinates of a Geometry, while keeping the same SRID.
 */
final class SwapXYProjector implements Projector
{
    #[Override]
    public function project(Point $point): Point
    {
        if ($point->isEmpty()) {
            return $point;
        }

        $coordinates = $point->toArray();

        /** @psalm-suppress PossiblyUndefinedArrayOffset */
        [$x, $y] = $coordinates;

        $coordinates[0] = $y;
        $coordinates[1] = $x;

        return new Point($point->coordinateSystem(), ...$coordinates);
    }

    #[Override]
    public function getTargetCoordinateSystem(CoordinateSystem $sourceCoordinateSystem): CoordinateSystem
    {
        return $sourceCoordinateSystem;
    }
}
