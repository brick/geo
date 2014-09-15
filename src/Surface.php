<?php

namespace Brick\Geo;

/**
 * A Surface is a two-dimensional geometry.
 * It is a noninstantiable class.
 */
abstract class Surface extends Geometry
{
    /**
     * The area of this Surface, as measured in the
     * spatial reference system of this Surface.
     *
     * @return float
     */
    abstract public function area();

    /**
     * The mathematical centroid for this Surface as a Point.
     * The result is not guaranteed to be on this Surface.
     *
     * @return Point
     */
    abstract public function centroid();

    /**
     * A Point guaranteed to be on this Surface.
     *
     * @return Point
     */
    abstract public function pointOnSurface();

    /**
     * {@inheritdoc}
     *
     * @return MultiCurve
     */
    public function boundary()
    {
        $boundary = parent::boundary();

        if (! $boundary instanceof MultiCurve) {
            throw new GeometryException('The boundary of a Surface is expected to be a MultiCurve');
        }

        return $boundary;
    }
}
