<?php

namespace Brick\Geo;

use Brick\Geo\Engine\GeometryEngineRegistry;

/**
 * A MultiSurface is a 2-dimensional GeometryCollection whose elements are Surfaces.
 *
 * All Surface elements use coordinates from the same coordinate reference system. The geometric interiors of any
 * two Surfaces in a MultiSurface may not intersect in the full coordinate system. The boundaries of any two coplanar
 * elements in a MultiSurface may intersect, at most, at a finite number of Points. If they were to meet along a curve,
 * they could be merged into a single surface.
 *
 * MultiSurface is an instantiable class in this Standard, and may be used to represent heterogeneous surfaces
 * collections of polygons and polyhedral surfaces. It defines a set of methods for its subclasses. The subclass of
 * MultiSurface is MultiPolygon corresponding to a collection of Polygons only. Other collections shall use
 * MultiSurface.
 */
abstract class MultiSurface extends GeometryCollection
{
    /**
     * Returns the area of this MultiSurface, as measured in the spatial reference system of this MultiSurface.
     *
     * @noproxy
     *
     * @return float
     */
    public function area()
    {
        return GeometryEngineRegistry::get()->area($this);
    }

    /**
     * Returns the mathematical centroid for this MultiSurface.
     *
     * The result is not guaranteed to be on this MultiSurface.
     *
     * @noproxy
     *
     * @return Point
     */
    public function centroid()
    {
        return GeometryEngineRegistry::get()->centroid($this);
    }

    /**
     * Returns a Point guaranteed to be on this MultiSurface.
     *
     * @noproxy
     *
     * @return Point
     */
    public function pointOnSurface()
    {
        return GeometryEngineRegistry::get()->pointOnSurface($this);
    }

    /**
     * {@inheritdoc}
     */
    protected static function containedGeometryType()
    {
        return Surface::class;
    }
}
