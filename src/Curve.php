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
     * The curved is closed if `startPoint()` == `endPoint()`.
     *
     * @return boolean
     */
    abstract public function isClosed();

    /**
     * Returns whether this Curve is closed and simple.
     *
     * The curved is closed if `startPoint()` == `endPoint()`.
     * The curved is simple if it does not pass through the same Point more than once.
     *
     * @return boolean
     */
    abstract public function isRing();
}
