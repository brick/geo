<?php

namespace Brick\Geo;

use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\EmptyGeometryException;
use Brick\Geo\Exception\InvalidGeometryException;

/**
 * A Triangle is a Polygon with 3 distinct, non-collinear vertices and no interior boundary.
 */
class Triangle extends Polygon
{
    /**
     * Class constructor.
     *
     * The coordinate system of each of the rings must match the one of the Polygon.
     *
     * @param CoordinateSystem $cs       The coordinate system of the Polygon.
     * @param LineString       ...$rings The rings that compose the Polygon.
     *
     * @throws InvalidGeometryException  If the number of points is not right, or interior rings are used.
     * @throws CoordinateSystemException If different coordinate systems are used.
     */
    public function __construct(CoordinateSystem $cs, LineString ...$rings)
    {
        parent::__construct($cs, ...$rings);

        if (! $this->isEmpty()) {
            if ($this->exteriorRing()->numPoints() !== 4) {
                throw new InvalidGeometryException('A triangle must have exactly 4 points.');
            }

            if ($this->numInteriorRings() !== 0) {
                throw new InvalidGeometryException('A triangle cannot have interior rings.');
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
