<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Attribute\NoProxy;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Projector\Projector;

/**
 * A Triangle is a Polygon with 3 distinct, non-collinear vertices and no interior boundary.
 */
class Triangle extends Polygon
{
    public function __construct(CoordinateSystem $cs, LineString ...$rings)
    {
        parent::__construct($cs, ...$rings);

        if ($this->isEmpty) {
            return;
        }

        if ($this->exteriorRing()->numPoints() !== 4) {
            throw new InvalidGeometryException('A triangle must have exactly 4 (3 + 1) points.');
        }

        if ($this->numInteriorRings() !== 0) {
            throw new InvalidGeometryException('A triangle must not have interior rings.');
        }
    }

    #[NoProxy]
    public function geometryType() : string
    {
        return 'Triangle';
    }

    #[NoProxy]
    public function geometryTypeBinary() : int
    {
        return Geometry::TRIANGLE;
    }

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
