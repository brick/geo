<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Attribute\NoProxy;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Projector\Projector;
use Override;

/**
 * A Triangle is a Polygon with 3 distinct, non-collinear vertices and no interior boundary.
 *
 * @final
 */
readonly class Triangle extends Polygon
{
    #[Override]
    protected function validate(): void
    {
        if ($this->isEmpty) {
            return;
        }

        if ($this->exteriorRing()->numPoints() !== 4) {
            throw new InvalidGeometryException('A Triangle must have exactly 4 (3 + first again) points.');
        }

        if ($this->numInteriorRings() !== 0) {
            throw new InvalidGeometryException('A Triangle must not have interior rings.');
        }
    }

    #[NoProxy, Override]
    public function geometryType() : string
    {
        return 'Triangle';
    }

    #[NoProxy, Override]
    public function geometryTypeBinary() : int
    {
        return Geometry::TRIANGLE;
    }

    #[Override]
    public function project(Projector $projector): Triangle
    {
        return new Triangle(
            $projector->getTargetCoordinateSystem($this->coordinateSystem),
            ...array_map(
                fn (LineString $ring) => $ring->project($projector),
                $this->rings,
            ),
        );
    }
}
