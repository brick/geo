<?php

declare(strict_types=1);

namespace Brick\Geo;

use ArrayIterator;
use Brick\Geo\Attribute\NoProxy;
use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\EmptyGeometryException;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Exception\NoSuchGeometryException;
use Brick\Geo\Projector\Projector;
use Override;

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
     * (optional) other ones represent the interior rings (holes) of the Polygon.
     *
     * An empty Polygon contains no rings.
     *
     * @psalm-var list<LineString>
     *
     * @var LineString[]
     */
    protected array $rings = [];

    /**
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

        $this->rings = array_values($rings);
    }

    /**
     * Creates a non-empty Polygon composed of the given rings.
     *
     * @psalm-suppress UnsafeInstantiation
     *
     * @param LineString    $exteriorRing  The exterior ring.
     * @param LineString ...$interiorRings The interior rings, if any.
     *
     * @throws InvalidGeometryException  If the resulting geometry is not valid for a sub-type of Polygon.
     * @throws CoordinateSystemException If the rings use different coordinate systems.
     */
    public static function of(LineString $exteriorRing, LineString ...$interiorRings) : Polygon
    {
        return new static($exteriorRing->coordinateSystem(), $exteriorRing, ...$interiorRings);
    }

    /**
     * Returns all rings in this Polygon, with the exterior ring first, then the interior rings.
     *
     * Returns an empty array if this Polygon is empty.
     *
     * @return LineString[]
     */
    public function rings(): array
    {
        return $this->rings;
    }

    /**
     * Returns the exterior ring of this Polygon.
     *
     * @throws EmptyGeometryException
     */
    public function exteriorRing() : LineString
    {
        if ($this->isEmpty) {
            throw new EmptyGeometryException('An empty Polygon has no exterior ring.');
        }

        return $this->rings[0];
    }

    /**
     * Returns the number of interior rings in this Polygon.
     */
    public function numInteriorRings() : int
    {
        if ($this->isEmpty) {
            return 0;
        }

        return count($this->rings) - 1;
    }

    /**
     * Returns the specified interior ring N in this Polygon.
     *
     * @param int $n The ring number, 1-based.
     *
     * @throws NoSuchGeometryException If there is no interior ring at this index.
     */
    public function interiorRingN(int $n) : LineString
    {
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
    public function interiorRings() : array
    {
        return array_slice($this->rings, 1);
    }

    #[NoProxy, Override]
    public function geometryType() : string
    {
        return 'Polygon';
    }

    #[NoProxy, Override]
    public function geometryTypeBinary() : int
    {
        return Geometry::POLYGON;
    }

    #[Override]
    public function getBoundingBox() : BoundingBox
    {
        $boundingBox = BoundingBox::new();

        foreach ($this->rings as $ring) {
            $boundingBox = $boundingBox->extendedWithBoundingBox($ring->getBoundingBox());
        }

        return $boundingBox;
    }

    /**
     * @return list<list<list<float>>>
     */
    #[Override]
    public function toArray() : array
    {
        return array_map(
            fn (LineString $ring) => $ring->toArray(),
            $this->rings,
        );
    }

    #[Override]
    public function project(Projector $projector): Polygon
    {
        return new Polygon(
            $projector->getTargetCoordinateSystem($this->coordinateSystem),
            ...array_map(
                fn (LineString $ring) => $ring->project($projector),
                $this->rings,
            ),
        );
    }

    /**
     * Returns the number of rings (exterior + interior) in this Polygon.
     *
     * Required by interface Countable.
     */
    #[Override]
    public function count() : int
    {
        return count($this->rings);
    }

    /**
     * Returns an iterator for the rings (exterior + interior) in this Polygon.
     *
     * Required by interface IteratorAggregate.
     *
     * @psalm-return ArrayIterator<int, LineString>
     */
    #[Override]
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->rings);
    }

    /**
     * Returns a copy of this Polygon, with the exterior ring replaced with the given one.
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public function withExteriorRing(LineString $exteriorRing) : Polygon
    {
        return new static($this->coordinateSystem, $exteriorRing, ...$this->interiorRings());
    }

    /**
     * Returns a copy of this Polygon, with the interior rings replaced with the given ones.
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public function withInteriorRings(LineString ...$interiorRings) : Polygon
    {
        return new static($this->coordinateSystem, $this->exteriorRing(), ...$interiorRings);
    }

    /**
     * Returns a copy of this Polygon, with the given interior rings added.
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public function withAddedInteriorRings(LineString ...$interiorRings) : Polygon
    {
        return new static($this->coordinateSystem, $this->exteriorRing(), ...$this->interiorRings(), ...$interiorRings);
    }
}
