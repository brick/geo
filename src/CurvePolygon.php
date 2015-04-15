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
     * @param Curve[] $rings
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     *
     * @return static
     *
     * @throws GeometryException
     */
    public static function create(array $rings, $is3D, $isMeasured, $srid = 0)
    {
        $is3D       = (bool) $is3D;
        $isMeasured = (bool) $isMeasured;

        $srid = (int) $srid;

        self::checkGeometries($rings, Curve::class, $is3D, $isMeasured, $srid);

        $CurvePolygon = new static(! $rings, $is3D, $isMeasured, $srid);
        $CurvePolygon->rings = array_values($rings);

        return $CurvePolygon;
    }

    /**
     * Returns the exterior ring of this CurvePolygon.
     *
     * @return LineString
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
     * @return LineString
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
