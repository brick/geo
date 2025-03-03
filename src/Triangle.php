<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Projector\Projector;
use Override;

/**
 * A Triangle is a Polygon with 3 distinct, non-collinear vertices and no interior boundary.
 */
final class Triangle extends Polygon
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

    #[Override]
    public function geometryType() : string
    {
        return 'Triangle';
    }

    #[Override]
    public function geometryTypeBinary() : int
    {
        return Geometry::TRIANGLE;
    }
}
