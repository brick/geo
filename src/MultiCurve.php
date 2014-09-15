<?php

namespace Brick\Geo;

/**
 * A MultiCurve is a geometry collection composed of Curve elements.
 * MultiCurve is a noninstantiable class.
 */
abstract class MultiCurve extends GeometryCollection
{
    /**
     * Returns true if this MultiCurve is closed
     * [startPoint() = endPoint() for each Curve in this MultiCurve].
     *
     * @return integer
     */
    abstract public function isClosed();

    /**
     * The Length of this MultiCurve which is equal
     * to the sum of the lengths of the element Curves.
     *
     * @return float
     */
    abstract public function length();
}
