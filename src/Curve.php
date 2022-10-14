<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Exception\EmptyGeometryException;

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
}
