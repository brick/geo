<?php

namespace Brick\Geo;

use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\EmptyGeometryException;
use Brick\Geo\Exception\NoSuchGeometryException;

/**
 * A CurvePolygon is a planar Surface defined by 1 exterior boundary and 0 or more interior boundaries.
 *
 * A CurvePolygon instance differs from a Polygon instance in that a CurvePolygon instance may contain
 * the following circular arc segments: CircularString and CompoundCurve in addition to LineString.
 */
class CurvePolygon extends Surface
{
    /**
     * The rings that compose this CurvePolygon.
     *
     * The first one represents the exterior ring, and the
     * (optional) other ones represent the interior rings of the CurvePolygon.
     *
     * An empty CurvePolygon contains no rings.
     *
     * @var Curve[]
     */
    protected $rings = [];

    /**
     * Class constructor.
     *
     * The coordinate system of each of the rings must match the one of the CurvePolygon.
     *
     * @param CoordinateSystem $cs       The coordinate system of the CurvePolygon.
     * @param Curve            ...$rings The rings that compose the CurvePolygon.
     *
     * @throws CoordinateSystemException If different coordinate systems are used.
     */
    public function __construct(CoordinateSystem $cs, Curve ...$rings)
    {
        parent::__construct($cs, ! $rings);

        if (! $rings) {
            return;
        }

        CoordinateSystem::check($this, ...$rings);

        $this->rings = $rings;
    }

    /**
     * Creates a non-empty CurvePolygon composed of the given rings.
     *
     * @param Curve    $exteriorRing  The exterior ring.
     * @param Curve ...$interiorRings The interior rings, if any.
     *
     * @return CurvePolygon
     *
     * @throws CoordinateSystemException If the rings use different coordinate systems.
     */
    public static function of(Curve $exteriorRing, Curve ...$interiorRings)
    {
        return new static($exteriorRing->coordinateSystem(), $exteriorRing, ...$interiorRings);
    }

    /**
     * Returns the exterior ring of this CurvePolygon.
     *
     * @return Curve
     *
     * @throws EmptyGeometryException
     */
    public function exteriorRing()
    {
        if ($this->isEmpty) {
            throw new EmptyGeometryException('An empty CurvePolygon has no exterior ring.');
        }

        return $this->rings[0];
    }

    /**
     * Returns the number of interior rings in this CurvePolygon.
     *
     * @return int
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
     * @param int $n The ring number, 1-based.
     *
     * @return Curve
     *
     * @throws NoSuchGeometryException If there is no interior ring at this index.
     */
    public function interiorRingN($n)
    {
        $n = (int) $n;

        if ($n === 0 || ! isset($this->rings[$n])) {
            throw new NoSuchGeometryException('There is no interior ring in this CurvePolygon at index ' . $n);
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
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryTypeBinary()
    {
        return Geometry::CURVEPOLYGON;
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
     * Returns the number of rings (exterior + interior) in this CurvePolygon.
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
     * Returns an iterator for the rings (exterior + interior) in this CurvePolygon.
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
