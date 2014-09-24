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
 * that when two LinearRings from these two Polygons traverse the common boundary segment, they do so in
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
class PolyhedralSurface extends Surface implements \Countable, \IteratorAggregate
{
    /**
     * An array of Polygon objects.
     *
     * @var array
     */
    protected $patches = [];

    /**
     * Class constructor.
     *
     * Internal use only, consumer code must use factory() instead.
     *
     * @param array $patches An array on Polygon objects.
     *
     * @throws GeometryException
     */
    protected function __construct(array $patches)
    {
        if (count($patches) == 0) {
            throw new GeometryException('A PolyhedralSurface must be constructed with at least one patch');
        }

        foreach ($patches as $patch) {
            $this->addPatch($patch);
        }
    }

    /**
     * Internal function for the constructor, to provide strong typing.
     *
     * @param Polygon $patch
     */
    private function addPatch(Polygon $patch)
    {
        $this->patches[] = $patch;
    }

    /**
     * Factory method to create a new PolyhedralSurface.
     *
     * @param Polygon[] $polygons
     *
     * @return PolyhedralSurface
     */
    public static function factory(array $polygons)
    {
        return new PolyhedralSurface($polygons);
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
     * The patch number is 1-based.
     *
     * @param integer $n
     *
     * @return Polygon
     *
     * @throws GeometryException
     */
    public function patchN($n)
    {
        if (! is_int($n)) {
            throw new GeometryException('The patch number must be an integer');
        }

        // decrement the index, as our array is 0-based
        $n--;

        if ($n < 0 || $n >= count($this->patches)) {
            throw new GeometryException('Patch number out of range');
        }

        return $this->patches[$n];
    }

    /**
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
     * @todo needs implementation
     *
     * @noproxy
     *
     * @return boolean
     *
     * @throws GeometryException
     */
    public function isClosed()
    {
        throw GeometryException::unimplementedMethod(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function pointOnSurface()
    {
        return $this->patches[0]->pointOnSurface();
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function area()
    {
        return GeometryEngineRegistry::get()->area($this);
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
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function dimension()
    {
        return 2;
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return false;
    }

    /**
     * Returns the total number of rings in this Polygon (exterior + interior).
     *
     * Required by interface Countable.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->patches);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->patches);
    }
}
