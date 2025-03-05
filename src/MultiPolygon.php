<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Attribute\NoProxy;
use Brick\Geo\Projector\Projector;
use Override;

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
 *
 * @extends MultiSurface<Polygon>
 */
class MultiPolygon extends MultiSurface
{
    /**
     * @return list<list<list<list<float>>>>
     */
    #[Override]
    public function toArray() : array
    {
        return array_map(
            fn(Polygon $polygon) => $polygon->toArray(),
            $this->geometries,
        );
    }

    #[NoProxy, Override]
    public function geometryType() : string
    {
        return 'MultiPolygon';
    }

    #[NoProxy, Override]
    public function geometryTypeBinary() : int
    {
        return Geometry::MULTIPOLYGON;
    }

    #[Override]
    public function dimension() : int
    {
        return 2;
    }

    #[Override]
    protected function containedGeometryType() : string
    {
        return Polygon::class;
    }

    #[Override]
    public function project(Projector $projector): MultiPolygon
    {
        return new MultiPolygon(
            $projector->getTargetCoordinateSystem($this->coordinateSystem),
            ...array_map(
                fn (Polygon $polygon) => $polygon->project($projector),
                $this->geometries,
            ),
        );
    }
}
