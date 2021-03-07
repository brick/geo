<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Exception\InvalidGeometryException;

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

    /**
     * @noproxy
     */
    public function geometryType() : string
    {
        return 'Triangle';
    }

    /**
     * @noproxy
     */
    public function geometryTypeBinary() : int
    {
        return Geometry::TRIANGLE;
    }
}
