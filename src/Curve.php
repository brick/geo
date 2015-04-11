<?php

namespace Brick\Geo;

use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Exception\GeometryException;

/**
 * A Curve is a 1-dimensional geometric object usually stored as a sequence of Points.
 *
 * The subtype of Curve specifies the form of the interpolation between Points.
 */
abstract class Curve extends Geometry
{
    /**
     * @noproxy
     *
     * {@inheritdoc}
     *
     * A Curve is a 1-dimensional geometric object.
     */
    public function dimension()
    {
        return 1;
    }

    /**
     * Returns the length of this Curve in its associated spatial reference.
     *
     * @noproxy
     *
     * @return float
     */
    public function length()
    {
        return GeometryEngineRegistry::get()->length($this);
    }

    /**
     * Returns the start Point of this Curve.
     *
     * @return Point
     *
     * @throws GeometryException If the curve is empty.
     */
    abstract public function startPoint();

    /**
     * Returns the end Point of this Curve.
     *
     * @return Point
     *
     * @throws GeometryException If the curve is empty.
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
        return GeometryEngineRegistry::get()->isClosed($this);
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
