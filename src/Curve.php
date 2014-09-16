<?php

namespace Brick\Geo;

/**
 * A Curve is a one-dimensional geometry, usually represented by a sequence of points.
 *
 * Particular subclasses of Curve define the type of interpolation between points.
 * Curve is a noninstantiable class.
 */
abstract class Curve extends Geometry
{
    /**
     * The length of this Curve in its associated spatial reference.
     *
     * @return float
     */
    abstract public function length();

    /**
     * The start Point of this Curve.
     *
     * @return Point
     */
    abstract public function startPoint();

    /**
     * The end Point of this Curve.
     *
     * @return Point
     */
    abstract public function endPoint();

    /**
     * Returns true if this Curve is closed [startPoint() = endPoint()].
     *
     * @return boolean
     */
    abstract public function isClosed();

    /**
     * Returns true if this Curve is closed [startPoint() = endPoint()]
     * and this Curve is simple (does not pass through the same Point more than once).
     *
     * @return boolean
     */
    abstract public function isRing();
}
