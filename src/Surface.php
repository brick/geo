<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Exception\GeometryEngineException;

/**
 * A Surface is a 2-dimensional geometric object.
 *
 * A simple Surface may consists of a single "patch" that is associated with one "exterior boundary" and 0 or more
 * "interior" boundaries. A single such Surface patch in 3-dimensional space is isometric to planar Surfaces, by a
 * simple affine rotation matrix that rotates the patch onto the plane z = 0. If the patch is not vertical,
 * the projection onto the same plane is an isomorphism, and can be represented as a linear transformation,
 * i.e. an affine.
 *
 * Polyhedral Surfaces are formed by "stitching" together such simple Surfaces patches along their common
 * boundaries. Such polyhedral Surfaces in a 3-dimensional space may not be planar as a whole, depending on the
 * orientation of their planar normals. If all the patches are in alignment (their normals are parallel),
 * then the whole stitched polyhedral surface is co-planar and can be represented as a single patch if it is connected.
 *
 * The boundary of a simple Surface is the set of closed Curves corresponding to its "exterior" and "interior"
 * boundaries.
 */
abstract class Surface extends Geometry
{
    /**
     * @noproxy
     *
     * A Surface is a 2-dimensional geometric object.
     */
    public function dimension() : int
    {
        return 2;
    }

    /**
     * Returns the area of this Surface, as measured in the spatial reference system of this Surface.
     *
     * @deprecated Please use `$geometryEngine->area()`.
     *
     * @noproxy
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function area() : float
    {
        return GeometryEngineRegistry::get()->area($this);
    }

    /**
     * Returns a Point guaranteed to be on this Surface.
     *
     * @deprecated Please use `$geometryEngine->pointOnSurface()`.
     *
     * @noproxy
     *
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function pointOnSurface() : Point
    {
        return GeometryEngineRegistry::get()->pointOnSurface($this);
    }
}
