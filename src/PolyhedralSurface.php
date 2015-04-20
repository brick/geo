<?php

namespace Brick\Geo;

use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Exception\GeometryException;

/**
 * A PolyhedralSurface is a contiguous collection of polygons, which share common boundary segments.
 *
 * For each pair of polygons that "touch", the common boundary shall be expressible as a finite collection
 * of LineStrings. Each such LineString shall be part of the boundary of at most 2 Polygon patches.
 *
 * For any two polygons that share a common boundary, the "top" of the polygon shall be consistent. This means 
 * that when two linear rings from these two Polygons traverse the common boundary segment, they do so in
 * opposite directions. Since the Polyhedral surface is contiguous, all polygons will be thus consistently oriented.
 * This means that a non-oriented surface (such as Möbius band) shall not have single surface representations.
 * They may be represented by a MultiSurface.
 *
 * If each such LineString is the boundary of exactly 2 Polygon patches, then the PolyhedralSurface is a simple,
 * closed polyhedron and is topologically isomorphic to the surface of a sphere. By the Jordan Surface Theorem
 * (Jordan’s Theorem for 2-spheres), such polyhedrons enclose a solid topologically isomorphic to the interior of a
 * sphere; the ball. In this case, the "top" of the surface will either point inward or outward of the enclosed
 * finite solid. If outward, the surface is the exterior boundary of the enclosed surface. If inward, the surface
 * is the interior of the infinite complement of the enclosed solid. A Ball with some number of voids (holes) inside
 * can thus be presented as one exterior boundary shell, and some number in interior boundary shells.
 */
class PolyhedralSurface extends Surface
{
    /**
     * The polygons that compose this PolyhedralSurface.
     *
     * An empty PolyhedralSurface contains no polygons.
     *
     * @var Polygon[]
     */
    protected $patches = [];

    /**
     * Class constructor.
     *
     * The coordinate system of each of the patches must match the one of the PolyhedralSurface.
     *
     * @param CoordinateSystem $cs         The coordinate system of the PolyhedralSurface.
     * @param Polygon          ...$patches The patches that compose the PolyhedralSurface.
     *
     * @throws GeometryException
     */
    public function __construct(CoordinateSystem $cs, Polygon ...$patches)
    {
        parent::__construct($cs, ! $patches);

        if (! $patches) {
            return;
        }

        foreach ($patches as $patch) {
            $cs->checkMatches($patch->coordinateSystem());
        }

        $this->patches = $patches;
    }

    /**
     * Returns a PolyhedralSurface composed of the given patches.
     *
     * All patches must be using the same coordinate system.
     * The coordinate system being inferred from the patches, an empty patch list is not allowed.
     * To create an empty PolyhedralSurface, use the class constructor instead.
     *
     * @param Polygon ...$patches The patches that compose the PolyhedralSurface.
     *
     * @return PolyhedralSurface
     *
     * @throws GeometryException
     */
    public static function of(Polygon ...$patches)
    {
        if (! $patches) {
            throw GeometryException::atLeastOneGeometryExpected(static::class, __FUNCTION__);
        }

        return new static($patches[0]->coordinateSystem(), ...$patches);
    }

    /**
     * @return integer
     */
    public function numPatches()
    {
        return count($this->patches);
    }

    /**
     * Returns the specified patch N in this PolyhedralSurface.
     *
     * @param integer $n The patch number, 1-based.
     *
     * @return Polygon
     *
     * @throws GeometryException If there is no patch at this index.
     */
    public function patchN($n)
    {
        $n = (int) $n;

        if (! isset($this->patches[$n - 1])) {
            throw new GeometryException('There is no patch in this PolyhedralSurface at index ' . $n);
        }

        return $this->patches[$n - 1];
    }

    /**
     * Returns the patches that compose this PolyhedralSurface.
     *
     * @return Polygon[]
     */
    public function patches()
    {
        return $this->patches;
    }

    /**
     * Returns the collection of polygons in this surface that bounds the given polygon 'p' for any polygon 'p' in the surface.
     *
     * @todo needs implementation
     *
     * @noproxy
     *
     * @param Polygon $p
     *
     * @return MultiPolygon
     *
     * @throws GeometryException
     */
    public function boundingPolygons(Polygon $p)
    {
        throw GeometryException::unimplementedMethod(__METHOD__);
    }

    /**
     * @noproxy
     *
     * @return boolean
     */
    public function isClosed()
    {
        return GeometryEngineRegistry::get()->isClosed($this);
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'PolyhedralSurface';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $result = [];

        foreach ($this->patches as $patch) {
            $result[] = $patch->toArray();
        }

        return $result;
    }

    /**
     * Returns the number of patches in this PolyhedralSurface.
     *
     * Required by interface Countable.
     *
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->patches);
    }

    /**
     * Returns an iterator for the patches in this PolyhedralSurface.
     *
     * Required by interface IteratorAggregate.
     *
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->patches);
    }

    /**
     * Returns the FQCN of the contained Geometry type.
     *
     * @return string
     */
    protected static function containedGeometryType()
    {
        return Polygon::class;
    }
}
