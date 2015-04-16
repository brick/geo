<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;

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
class Polygon extends Surface implements \Countable, \IteratorAggregate
{
    /**
     * The rings that compose this polygon.
     *
     * The first one represents the exterior ring, and the
     * (optional) other ones represent the interior rings of the Polygon.
     *
     * @var LineString[]
     */
    protected $rings = [];

    /**
     * @param LineString[]          $rings
     * @param CoordinateSystem|null $cs
     *
     * @return static
     *
     * @throws GeometryException
     */
    public static function create(array $rings, CoordinateSystem $cs = null)
    {
        $cs = self::checkGeometries($rings, LineString::class, $cs);

        $polygon = new static($cs, ! $rings);
        $polygon->rings = array_values($rings);

        return $polygon;
    }

    /**
     * @deprecated Use create() instead.
     *
     * @param LineString[] $rings
     *
     * @return Polygon
     *
     * @throws GeometryException
     */
    public static function factory(array $rings)
    {
        if (count($rings) === 1 && count(reset($rings)) === 3 + 1) {
            return Triangle::create($rings);
        }

        return Polygon::create($rings);
    }

    /**
     * Creates a rectangle from two corner 2D points.
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

        $ring = LineString::create([$p1, $p2, $p3, $p4, $p1]);

        return Polygon::create([$ring]);
    }

    /**
     * Returns the exterior ring of this Polygon.
     *
     * @return LineString
     *
     * @throws GeometryException
     */
    public function exteriorRing()
    {
        if ($this->isEmpty) {
            throw new GeometryException('An empty Polygon has no exterior ring.');
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
     * @throws GeometryException If there is no interior ring at this index.
     */
    public function interiorRingN($n)
    {
        $n = (int) $n;

        if ($n === 0 || ! isset($this->rings[$n])) {
            throw new GeometryException('There is no interior ring in this Polygon at index ' . $n);
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
     * Returns the total number of rings in this Polygon (exterior + interior).
     *
     * Required by interface Countable.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->rings);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->rings);
    }
}
