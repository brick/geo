<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Engine\GeometryEngine;
use Brick\Geo\Exception\GeometryEngineException;

/**
 * A MultiCurve is a 1-dimensional GeometryCollection whose elements are Curves.
 *
 * MultiCurve is a non-instantiable class in this standard; it defines a set of methods for its subclasses and is
 * included for reasons of extensibility.
 *
 * A MultiCurve is simple if and only if all of its elements are simple and the only intersections between any two
 * elements occur at Points that are on the boundaries of both elements.
 *
 * The boundary of a MultiCurve is obtained by applying the "mod 2" union rule: A Point is in the boundary of a
 * MultiCurve if it is in the boundaries of an odd number of elements of the MultiCurve.
 *
 * A MultiCurve is closed if all of its elements are closed. The boundary of a closed MultiCurve is always empty.
 *
 * A MultiCurve is defined as topologically closed.
 *
 * @template T of Curve
 * @extends GeometryCollection<T>
 */
abstract class MultiCurve extends GeometryCollection
{
}
