<?php

namespace Brick\Geo;

/**
 * A MultiCurve is a geometry collection composed of Curve elements.
 *
 * MultiCurve is a non-instantiable class.
 */
abstract class MultiCurve extends GeometryCollection
{
    /**
     * Returns true if this MultiCurve is closed.
     *
     * The MultiCurve is considered closed if each element curve is closed.
     *
     * @return integer
     */
    abstract public function isClosed();

    /**
     * Returns the length of this MultiCurve.
     *
     * The length is equal to the sum of the lengths of the element Curves.
     *
     * @return float
     */
    abstract public function length();
}
