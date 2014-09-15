<?php

namespace Brick\Geo;

/**
 * A MultiSurface is a geometry collection composed of surface elements.
 * MultiSurface is a noninstantiable class.
 */
abstract class MultiSurface extends GeometryCollection
{
    /**
     * The area of this MultiSurface, as measured in
     * the spatial reference system of this MultiSurface.
     *
     * @return float
     */
    abstract public function area();

    /**
     * The mathematical centroid for this MultiSurface.
     * The result is not guaranteed to be on this MultiSurface.
     *
     * @return Point
     */
    abstract public function centroid();

    /**
     * A Point guaranteed to be on this MultiSurface.
     *
     * @return Point
     */
    abstract public function pointOnSurface();
}
