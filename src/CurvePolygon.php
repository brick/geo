<?php

declare(strict_types=1);

namespace Brick\Geo;

use ArrayIterator;
use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\EmptyGeometryException;
use Brick\Geo\Exception\NoSuchGeometryException;
use Brick\Geo\Projector\Projector;
use Override;

/**
 * A CurvePolygon is a planar Surface defined by 1 exterior boundary and 0 or more interior boundaries.
 *
 * A CurvePolygon instance differs from a Polygon instance in that a CurvePolygon instance may contain
 * the following circular arc segments: CircularString and CompoundCurve in addition to LineString.
 *
 * @template-implements \IteratorAggregate<int<0, max>, Curve>
 */
final class CurvePolygon extends Surface implements \Countable, \IteratorAggregate
{
    /**
     * The rings that compose this CurvePolygon.
     *
     * The first one represents the exterior ring, and the
     * (optional) other ones represent the interior rings (holes) of the CurvePolygon.
     *
     * An empty CurvePolygon contains no rings.
     *
     * @var list<Curve>
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

        $this->rings = array_values($rings);
    }

    /**
     * Creates a non-empty CurvePolygon composed of the given rings.
     *
     * @param Curve    $exteriorRing  The exterior ring.
     * @param Curve ...$interiorRings The interior rings, if any.
     *
     * @throws CoordinateSystemException If the rings use different coordinate systems.
     */
    public static function of(Curve $exteriorRing, Curve ...$interiorRings) : CurvePolygon
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
     * @return list<Curve>
     */
    public function interiorRings() : array
    {
        return array_slice($this->rings, 1);
    }

    #[Override]
    public function geometryType() : string
    {
        return 'CurvePolygon';
    }

    #[Override]
    public function geometryTypeBinary() : int
    {
        return Geometry::CURVEPOLYGON;
    }

    #[Override]
    public function getBoundingBox() : BoundingBox
    {
        return array_reduce(
            $this->rings,
            fn (BoundingBox $boundingBox, Curve $ring) => $boundingBox->extendedWithBoundingBox($ring->getBoundingBox()),
            BoundingBox::new(),
        );
    }

    #[Override]
    public function toArray() : array
    {
        return array_map(
            fn (Curve $ring) => $ring->toArray(),
            $this->rings,
        );
    }

    #[Override]
    public function project(Projector $projector): CurvePolygon
    {
        return new CurvePolygon(
            $projector->getTargetCoordinateSystem($this->coordinateSystem),
            ...array_map(
                fn (Curve $ring) => $ring->project($projector),
                $this->rings,
            ),
        );
    }

    /**
     * Returns the number of rings (exterior + interior) in this CurvePolygon.
     */
    #[Override]
    public function count() : int
    {
        return count($this->rings);
    }

    /**
     * Returns an iterator for the rings (exterior + interior) in this CurvePolygon.
     *
     * @return ArrayIterator<int<0, max>, Curve>
     */
    #[Override]
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->rings);
    }

    /**
     * Returns a copy of this CurvePolygon, with the exterior ring replaced with the given one.
     */
    public function withExteriorRing(Curve $exteriorRing) : CurvePolygon
    {
        return new CurvePolygon($this->coordinateSystem, $exteriorRing, ...$this->interiorRings());
    }

    /**
     * Returns a copy of this CurvePolygon, with the interior rings replaced with the given ones.
     */
    public function withInteriorRings(Curve ...$interiorRings) : CurvePolygon
    {
        return new CurvePolygon($this->coordinateSystem, $this->exteriorRing(), ...$interiorRings);
    }

    /**
     * Returns a copy of this CurvePolygon, with the given interior rings added.
     */
    public function withAddedInteriorRings(Curve ...$interiorRings) : CurvePolygon
    {
        return new CurvePolygon($this->coordinateSystem, $this->exteriorRing(), ...$this->interiorRings(), ...$interiorRings);
    }
}
