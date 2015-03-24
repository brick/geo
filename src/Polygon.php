<?php

namespace Brick\Geo;

use Brick\Geo\Engine\GeometryEngineRegistry;
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
     * @param LineString[] $rings
     *
     * @return Polygon
     *
     * @throws GeometryException
     */
    public static function factory(array $rings)
    {
        foreach ($rings as $ring) {
            if (! $ring instanceof LineString) {
                throw GeometryException::unexpectedGeometryType(LineString::class, $ring);
            }
        }

        if (! $rings) {
            throw new GeometryException('A Polygon must have at least 1 ring (the exterior ring).');
        }

        self::getDimensions($rings, $is3D, $isMeasured, $srid);

        if (count($rings) === 1 && count(reset($rings)) === 3 + 1) {
            $polygon = new Triangle(false, $is3D, $isMeasured, $srid);
        } else {
            $polygon = new Polygon(false, $is3D, $isMeasured, $srid);
        }

        $polygon->rings = array_values($rings);

        return $polygon;
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

        $ring = LineString::factory([$p1, $p2, $p3, $p4, $p1]);

        return Polygon::factory([$ring]);
    }

    /**
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     *
     * @return Polygon
     */
    public static function polygonEmpty($is3D, $isMeasured, $srid)
    {
        return new Polygon(true, $is3D, $isMeasured, $srid);
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
    public function pointOnSurface()
    {
        if ($this->isEmpty) {
            return Point::pointEmpty($this->is3D, $this->isMeasured, $this->srid);
        }

        return $this->exteriorRing()->startPoint();
    }

    /**
     * Returns the exterior ring of this Polygon.
     *
     * @return LineString
     */
    public function exteriorRing()
    {
        if ($this->isEmpty) {
            return LineString::lineStringEmpty($this->is3D, $this->isMeasured, $this->srid);
        }

        return reset($this->rings);
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
     * Returns the Nth interior ring for this Polygon as a LineString.
     *
     * The ring number is 1-based.
     *
     * @param integer $n
     *
     * @return LineString
     *
     * @throws GeometryException
     */
    public function interiorRingN($n)
    {
        $n = (int) $n;

        if (! isset($this->rings[$n])) {
            throw new GeometryException('Ring number out of range.');
        }

        return $this->rings[$n];
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
     *
     * A Polygon is a 2-dimensional geometric object.
     */
    public function dimension()
    {
        return 2;
    }

    /**
     * Returns a nested array representing the coordinates of this Polygon.
     *
     * @return array
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
