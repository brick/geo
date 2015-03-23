<?php

namespace Brick\Geo;

/**
 * A Curve is a 1-dimensional geometric object usually stored as a sequence of Points.
 *
 * The subtype of Curve specifies the form of the interpolation between Points.
 * This standard defines only one subclass of Curve, LineString, which uses linear interpolation between Points.
 */
abstract class Curve extends Geometry
{
    /**
     * Returns the length of this Curve in its associated spatial reference.
     *
     * @return float
     */
    abstract public function length();

    /**
     * Returns the start Point of this Curve.
     *
     * @return Point
     */
    abstract public function startPoint();

    /**
     * Returns the end Point of this Curve.
     *
     * @return Point
     */
    abstract public function endPoint();

    /**
     * Returns whether this Curve is closed.
     *
     * The curve is closed if `startPoint()` == `endPoint()`.
     *
     * @noproxy
     *
     * @return boolean
     */
    public function isClosed()
    {
        return $this->startPoint()->equals($this->endPoint());
    }

    /**
     * Returns whether this Curve is a ring.
     *
     * The curve is a ring if it is both closed and simple.
     *
     * The curve is closed if its start point is equal to its end point.
     * The curve is simple if it does not pass through the same point more than once.
     *
     * @noproxy
     *
     * @return boolean
     */
    public function isRing()
    {
        return $this->isClosed() && $this->isSimple();
    }
}
