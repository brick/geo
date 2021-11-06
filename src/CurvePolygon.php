<?php

declare(strict_types=1);

namespace Brick\Geo;

use ArrayIterator;
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
     * @psalm-var list<Curve>
     *
     * @var Curve[]
     */
    protected array $rings = [];

    /**
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
     * @psalm-suppress UnsafeInstantiation
     *
     * @param Curve    $exteriorRing  The exterior ring.
     * @param Curve ...$interiorRings The interior rings, if any.
     *
     * @throws CoordinateSystemException If the rings use different coordinate systems.
     */
    public static function of(Curve $exteriorRing, Curve ...$interiorRings) :  CurvePolygon
    {
        return new static($exteriorRing->coordinateSystem(), $exteriorRing, ...$interiorRings);
    }

    /**
     * Returns the exterior ring of this CurvePolygon.
     *
     * @throws EmptyGeometryException
     */
    public function exteriorRing() : Curve
    {
        if ($this->isEmpty) {
            throw new EmptyGeometryException('An empty CurvePolygon has no exterior ring.');
        }

        return $this->rings[0];
    }

    /**
     * Returns the number of interior rings in this CurvePolygon.
     */
    public function numInteriorRings() : int
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
     * @throws NoSuchGeometryException If there is no interior ring at this index.
     */
    public function interiorRingN(int $n) : Curve
    {
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
    public function interiorRings() : array
    {
        return array_slice($this->rings, 1);
    }

    /**
     * @noproxy
     */
    public function geometryType() : string
    {
        return 'CurvePolygon';
    }

    /**
     * @noproxy
     */
    public function geometryTypeBinary() : int
    {
        return Geometry::CURVEPOLYGON;
    }

    public function toXY(): CurvePolygon
    {
        if ($this->coordinateDimension() === 2) {
            return $this;
        }

        $cs = $this->coordinateSystem
            ->withZ(false)
            ->withM(false);

        $rings = array_map(fn(Curve $ring) => $ring->toXY(), $this->rings);

        return new CurvePolygon($cs, ...$rings);
    }

    public function withoutZ(): CurvePolygon
    {
        if (! $this->coordinateSystem->hasZ()) {
            return $this;
        }

        $cs = $this->coordinateSystem->withZ(false);

        $rings = array_map(fn(Curve $ring) => $ring->withoutZ(), $this->rings);

        return new CurvePolygon($cs, ...$rings);
    }

    public function withoutM(): CurvePolygon
    {
        if (! $this->coordinateSystem->hasM()) {
            return $this;
        }

        $cs = $this->coordinateSystem->withM(false);

        $rings = array_map(fn(Curve $ring) => $ring->withoutM(), $this->rings);

        return new CurvePolygon($cs, ...$rings);
    }

    public function getBoundingBox() : BoundingBox
    {
        $boundingBox = new BoundingBox();

        foreach ($this->rings as $ring) {
            $boundingBox = $boundingBox->extendedWithBoundingBox($ring->getBoundingBox());
        }

        return $boundingBox;
    }

    public function toArray() : array
    {
        $result = [];

        foreach ($this->rings as $ring) {
            $result[] = $ring->toArray();
        }

        return $result;
    }

    public function swapXY() : Geometry
    {
        $that = clone $this;

        foreach ($that->rings as & $ring) {
            $ring = $ring->swapXY();
        }

        return $that;
    }

    /**
     * Returns the number of rings (exterior + interior) in this CurvePolygon.
     *
     * Required by interface Countable.
     */
    public function count() : int
    {
        return count($this->rings);
    }

    /**
     * Returns an iterator for the rings (exterior + interior) in this CurvePolygon.
     *
     * Required by interface IteratorAggregate.
     *
     * @psalm-return ArrayIterator<int, Curve>
     */
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->rings);
    }
}
