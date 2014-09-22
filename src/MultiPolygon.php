<?php

namespace Brick\Geo;

use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Exception\GeometryException;

/**
 * A MultiPolygon is a MultiSurface whose elements are Polygons.
 *
 * The assertions for MultiPolygons are as follows:
 *
 * a) The interiors of 2 Polygons that are elements of a MultiPolygon may not intersect;
 * b) The boundaries of any 2 Polygons that are elements of a MultiPolygon may not "cross" and may touch at only
 * a finite number of Points;
 * c) A MultiPolygon is defined as topologically closed;
 * d) A MultiPolygon may not have cut lines, spikes or punctures, a MultiPolygon is a regular closed Point set;
 * e) The interior of a MultiPolygon with more than 1 Polygon is not connected; the number of connected
 * components of the interior of a MultiPolygon is equal to the number of Polygons in the MultiPolygon.
 *
 * The boundary of a MultiPolygon is a set of closed Curves (LineStrings) corresponding to the boundaries of its
 * element Polygons. Each Curve in the boundary of the MultiPolygon is in the boundary of exactly 1 element 
 * Polygon, and every Curve in the boundary of an element Polygon is in the boundary of the MultiPolygon.
 */
class MultiPolygon extends MultiSurface
{
    /**
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'MultiPolygon';
    }

    /**
     * {@inheritdoc}
     */
    public function area()
    {
        return GeometryEngineRegistry::get()->area($this);
    }

    /**
     * {@inheritdoc}
     */
    public function centroid()
    {
        return GeometryEngineRegistry::get()->centroid($this);
    }

    /**
     * {@inheritdoc}
     */
    public function pointOnSurface()
    {
        throw GeometryException::unimplementedMethod(__METHOD__);
    }

    /**
     * Returns the polygons that compose this multipolygon.
     *
     * @return MultiPolygon[]
     */
    public function getPolygons()
    {
        return $this->geometries;
    }

    /**
     * {@inheritdoc}
     */
    protected static function containedGeometryType()
    {
        return Polygon::class;
    }
}
