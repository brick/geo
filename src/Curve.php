<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Exception\EmptyGeometryException;
use Brick\Geo\Exception\GeometryEngineException;

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
     * A Curve is a 1-dimensional geometric object.
     */
    public function dimension() : int
    {
        return 1;
    }

    /**
     * Returns the length of this Curve in its associated spatial reference.
     *
     * @deprecated Please use `$geometryEngine->length()`.
     *
     * @noproxy
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function length() : float
    {
        return GeometryEngineRegistry::get()->length($this);
    }

    /**
     * Returns the start Point of this Curve.
     *
     * @throws EmptyGeometryException If the curve is empty.
     */
    abstract public function startPoint() : Point;

    /**
     * Returns the end Point of this Curve.
     *
     * @throws EmptyGeometryException If the curve is empty.
     */
    abstract public function endPoint() : Point;

    /**
     * Returns whether this Curve is closed.
     *
     * The curve is closed if `startPoint()` == `endPoint()`.
     *
     * @deprecated Please use `$geometryEngine->isClosed()`.
     *
     * @noproxy
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function isClosed() : bool
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
     * @deprecated Please use `$geometryEngine->isClosed() && $geometryEngine->isSimple()`.
     *             Note that the next version (v0.8) will have a `$geometryEngine->isRing()` method.
     *
     * @noproxy
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function isRing() : bool
    {
        return $this->isClosed() && $this->isSimple();
    }
}
