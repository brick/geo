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
class PolyhedralSurface extends Surface implements \Countable, \IteratorAggregate
{
    /**
     * The polygons that compose this PolyhedralSurface.
     *
     * @var Polygon[]
     */
    protected $patches = [];

    /**
     * @param Polygon[] $patches
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return PolyhedralSurface
     *
     * @throws GeometryException
     */
    public static function create(array $patches, $is3D, $isMeasured, $srid)
    {
        $is3D       = (bool) $is3D;
        $isMeasured = (bool) $isMeasured;

        $srid = (int) $srid;

        self::checkGeometries($patches, Polygon::class, $is3D, $isMeasured, $srid);

        $polyhedralSurface = new static(! $patches, $is3D, $isMeasured, $srid);
        $polyhedralSurface->patches = array_values($patches);

        return $polyhedralSurface;
    }

    /**
     * Factory method to create a new PolyhedralSurface.
     *
     * @param Polygon[] $patches
     *
     * @return PolyhedralSurface
     *
     * @throws GeometryException
     */
    public static function factory(array $patches)
    {
        if (! $patches) {
            throw new GeometryException('A PolyhedralSurface must be constructed with at least one patch.');
        }

        $geometryType = static::containedGeometryType();

        foreach ($patches as $patch) {
            if (! $patch instanceof $geometryType) {
                throw GeometryException::unexpectedGeometryType($geometryType, $patch);
            }
        }

        self::getDimensions($patches, $is3D, $isMeasured, $srid);

        $polyhedralSurface = new static(false, $is3D, $isMeasured, $srid);
        $polyhedralSurface->patches = array_values($patches);

        return $polyhedralSurface;
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
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function dimension()
    {
        return 2;
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
