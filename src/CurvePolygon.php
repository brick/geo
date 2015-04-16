<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;

/**
 * A CurvePolygon is a planar Surface defined by 1 exterior boundary and 0 or more interior boundaries.
 *
 * A CurvePolygon instance differs from a Polygon instance in that a CurvePolygon instance may contain
 * the following circular arc segments: CircularString and CompoundCurve in addition to LineString.
 */
class CurvePolygon extends Surface implements \Countable, \IteratorAggregate
{
    /**
     * The rings that compose this CurvePolygon.
     *
     * The first one represents the exterior ring, and the
     * (optional) other ones represent the interior rings of the CurvePolygon.
     *
     * @var Curve[]
     */
    protected $rings = [];

    /**
     * @param Curve[]               $rings
     * @param CoordinateSystem|null $cs
     *
     * @return static
     *
     * @throws GeometryException
     */
    public static function create(array $rings, CoordinateSystem $cs = null)
    {
        $cs = self::checkGeometries($rings, Curve::class, $cs);

        $CurvePolygon = new static($cs, ! $rings);
        $CurvePolygon->rings = array_values($rings);

        return $CurvePolygon;
    }

    /**
     * Returns the exterior ring of this CurvePolygon.
     *
     * @return Curve
     *
     * @throws GeometryException
     */
    public function exteriorRing()
    {
        if ($this->isEmpty) {
            throw new GeometryException('An empty CurvePolygon has no exterior ring.');
        }

        return $this->rings[0];
    }

    /**
     * Returns the number of interior rings in this CurvePolygon.
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
     * Returns the specified interior ring N in this CurvePolygon.
     *
     * @param integer $n The ring number, 1-based.
     *
     * @return Curve
     *
     * @throws GeometryException If there is no interior ring at this index.
     */
    public function interiorRingN($n)
    {
        $n = (int) $n;

        if ($n === 0 || ! isset($this->rings[$n])) {
            throw new GeometryException('There is no interior ring in this CurvePolygon at index ' . $n);
        }

        return $this->rings[$n];
    }

    /**
     * Returns the interior rings in this CurvePolygon.
     *
     * @return Curve[]
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
        return 'CurvePolygon';
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
     * Returns the total number of rings in this CurvePolygon (exterior + interior).
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
