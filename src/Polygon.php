<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;
use Brick\Geo\Exception\EmptyGeometryException;
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
     * @param LineString       ...$rings The rings that compose the Polygon.
     *
     * @throws GeometryException
     */
    public function __construct(CoordinateSystem $cs, LineString ...$rings)
    {
        parent::__construct($cs, ! $rings);

        if (! $rings) {
            return;
        }

        foreach ($rings as $ring) {
            $cs->checkMatches($ring->coordinateSystem());
        }

        $this->rings = $rings;
    }

    /**
     * Returns a Polygon composed of the given rings.
     *
     * All rings must be using the same coordinate system.
     * The coordinate system being inferred from the rings, an empty ring list is not allowed.
     * To create an empty Polygon, use the class constructor instead.
     *
     * @param LineString ...$rings The rings that compose the Polygon.
     *
     * @return Polygon
     *
     * @throws GeometryException
     */
    public static function of(LineString ...$rings)
    {
        if (! $rings) {
            throw GeometryException::atLeastOneGeometryExpected(static::class, __FUNCTION__);
        }

        return new static($rings[0]->coordinateSystem(), ...$rings);
    }

    /**
     * Creates a rectangle from two corner 2D points.
     *
     * @deprecated Will be removed soon.
     *
     * @param Point $a The first corner point.
     * @param Point $b The second corner point.
     *
     * @return Polygon
     */
    public static function createRectangle(Point $a, Point $b)
    {
        $x1 = min($a->x(), $b->x());
        $x2 = max($a->x(), $b->x());

        $y1 = min($a->y(), $b->y());
        $y2 = max($a->y(), $b->y());

        $p1 = Point::xy($x1, $y1);
        $p2 = Point::xy($x2, $y1);
        $p3 = Point::xy($x2, $y2);
        $p4 = Point::xy($x1, $y2);

        $ring = LineString::of($p1, $p2, $p3, $p4, $p1);

        return Polygon::of($ring);
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
