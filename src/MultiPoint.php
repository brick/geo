<?php

declare(strict_types=1);

namespace Brick\Geo;

/**
 * A MultiPoint is a 0-dimensional GeometryCollection. The elements of a MultiPoint are restricted to Points.
 *
 * The Points are not connected or ordered in any semantically important way
 * (see the discussion at GeometryCollection). A MultiPoint is simple if no two Points in the MultiPoint are equal
 * (have identical coordinate values in X and Y).
 *
 * The boundary of a MultiPoint is the empty set.
 *
 * @extends GeometryCollection<Point>
 */
class MultiPoint extends GeometryCollection
{
    /**
     * @noproxy
     */
    public function geometryType() : string
    {
        return 'MultiPoint';
    }

    /**
     * @noproxy
     */
    public function geometryTypeBinary() : int
    {
        return Geometry::MULTIPOINT;
    }

    public function dimension() : int
    {
        return 0;
    }

    protected function containedGeometryType() : string
    {
        return Point::class;
    }
}
