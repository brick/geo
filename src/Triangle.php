<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;

/**
 * A Triangle is a Polygon with 3 distinct, non-collinear vertices and no interior boundary.
 */
class Triangle extends Polygon
{
    /**
     * {@inheritdoc}
     */
    public function __construct(CoordinateSystem $cs, LineString ...$rings)
    {
        parent::__construct($cs, ...$rings);

        if (! $this->isEmpty()) {
            if ($this->exteriorRing()->numPoints() !== 4) {
                throw new GeometryException('A triangle must have exactly 4 points.');
            }

            if ($this->numInteriorRings() !== 0) {
                throw new GeometryException('A triangle cannot have interior rings.');
            }
        }
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'Triangle';
    }
}
