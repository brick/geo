<?php

namespace Brick\Geo;

use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\EmptyGeometryException;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Exception\NoSuchGeometryException;

/**
 * A Polygon is a planar Surface defined by 1 exterior boundary and 0 or more interior boundaries.
 *
 * Each interior boundary defines a hole in the Polygon.
 *
 * The exterior boundary linear ring defines the “top” of the surface which is the side of the surface from which the
 * exterior boundary appears to traverse the boundary in a counter clockwise direction. The interior linear rings will
 * have the opposite orientation, and appear as clockwise when viewed from the “top”.
 *
 * The assertions for Polygons (the rules that define valid Polygons) are as follows:
 *
 * a) Polygons are topologically closed;
 * b) The boundary of a Polygon consists of a set of linear rings that make up its exterior and interior boundaries;
 * c) No two Rings in the boundary cross and the Rings in the boundary of a Polygon may intersect at a Point but
 * only as a tangent;
 * d) A Polygon may not have cut lines, spikes or punctures;
 * e) The interior of every Polygon is a connected point set;
 * f) The exterior of a Polygon with 1 or more holes is not connected. Each hole defines a connected component of
 * the exterior.
 *
 * In the above assertions, interior, closure and exterior have the standard topological definitions. The combination
 * of (a) and (c) makes a Polygon a regular closed Point set. Polygons are simple geometric objects.
 */
class Polygon extends Surface
{
    /**
     * The rings that compose this polygon.
     *
     * The first one represents the exterior ring, and the
     * (optional) other ones represent the interior rings of the Polygon.
     *
     * An empty Polygon contains no rings.
     *
     * @var LineString[]
     */
    protected $rings = [];

    /**
     * Class constructor.
     *
     * The coordinate system of each of the rings must match the one of the Polygon.
     *
     * @param CoordinateSystem $cs       The coordinate system of the Polygon.
     * @param LineString       ...$rings The rings that compose the Polygon, the first one being the exterior ring.
     *
     * @throws InvalidGeometryException  If the resulting geometry is not valid for a sub-type of Polygon.
     * @throws CoordinateSystemException If different coordinate systems are used.
     */
    public function __construct(CoordinateSystem $cs, LineString ...$rings)
    {
        parent::__construct($cs, ! $rings);

        if (! $rings) {
            return;
        }

        CoordinateSystem::check($this, ...$rings);

        $this->rings = $rings;
    }

    /**
     * Creates a non-empty Polygon composed of the given rings.
     *
     * @param LineString    $exteriorRing  The exterior ring.
     * @param LineString ...$interiorRings The interior rings, if any.
     *
     * @return Polygon
     *
     * @throws InvalidGeometryException  If the resulting geometry is not valid for a sub-type of Polygon.
     * @throws CoordinateSystemException If the rings use different coordinate systems.
     */
    public static function of(LineString $exteriorRing, LineString ...$interiorRings)
    {
        return new static($exteriorRing->coordinateSystem(), $exteriorRing, ...$interiorRings);
    }

    /**
     * Returns the exterior ring of this Polygon.
     *
     * @return LineString
     *
     * @throws EmptyGeometryException
     */
    public function exteriorRing()
    {
        if ($this->isEmpty) {
            throw new EmptyGeometryException('An empty Polygon has no exterior ring.');
        }

        return $this->rings[0];
    }

    /**
     * Returns the number of interior rings in this Polygon.
     *
     * @return integer
     */
    public function numInteriorRings()
    {
        if ($this->isEmpty) {
            return 0;
        }

        return count($this->rings) - 1;
    }

    /**
     * Returns the specified interior ring N in this Polygon.
     *
     * @param integer $n The ring number, 1-based.
     *
     * @return LineString
     *
     * @throws NoSuchGeometryException If there is no interior ring at this index.
     */
    public function interiorRingN($n)
    {
        $n = (int) $n;

        if ($n === 0 || ! isset($this->rings[$n])) {
            throw new NoSuchGeometryException('There is no interior ring in this Polygon at index ' . $n);
        }

        return $this->rings[$n];
    }

    /**
     * Returns the interior rings in this Polygon.
     *
     * @return LineString[]
     */
    public function interiorRings()
    {
        return array_slice($this->rings, 1);
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'Polygon';
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryTypeBinary()
    {
        return Geometry::POLYGON;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $result = [];

        foreach ($this->rings as $ring) {
            $result[] = $ring->toArray();
        }

        return $result;
    }

    /**
     * Returns the number of rings (exterior + interior) in this Polygon.
     *
     * Required by interface Countable.
     *
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->rings);
    }

    /**
     * Returns an iterator for the rings (exterior + interior) in this Polygon.
     *
     * Required by interface IteratorAggregate.
     *
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->rings);
    }
}
