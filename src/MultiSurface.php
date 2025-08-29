<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Projector\Projector;
use Exception;
use Override;

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
 *
 * @template T of Surface
 *
 * @extends GeometryCollection<T>
 */
abstract class MultiSurface extends GeometryCollection
{
    #[Override]
    public function project(Projector $projector): MultiSurface
    {
        throw new Exception(
            'This exception should never be thrown. ' .
            'This method is here to ensure that MultiSurface::project() has the correct return type, ' .
            'and force concrete classes below MultiSurface to return a MultiSurface, too. ' .
            'It cannot be made abstract because GeometryCollection::project() is not abstract.',
        );
    }
}
