<?php

declare(strict_types=1);

namespace Brick\Geo\Projector;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Point;
use Override;

/**
 * Rounds coordinates to a given precision.
 * This projector is typically used to simplify the WKT representation of geometries.
 */
final readonly class RoundCoordinatesProjector implements Projector
{
    public function __construct(
        private int $precision,
    ) {
    }

    #[Override]
    public function project(Point $point): Point
    {
        $coords = array_map(
            fn (float $coord): float => round($coord, $this->precision),
            $point->toArray(),
        );

        return new Point($point->coordinateSystem(), ...$coords);
    }

    #[Override]
    public function getTargetCoordinateSystem(CoordinateSystem $sourceCoordinateSystem): CoordinateSystem
    {
        return $sourceCoordinateSystem;
    }
}
