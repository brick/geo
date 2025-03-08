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
 * A CircularString is a Curve made of zero or more connected circular arc segments.
 *
 * A circular arc segment is a curved segment defined by three points in a two-dimensional plane;
 * the first point cannot be the same as the third point.
 */
class CircularString extends Curve
{
    /**
     * The Points that compose this CircularString.
     *
     * An empty CircularString contains no points.
     *
     * @psalm-var list<Point>
     *
     * @var Point[]
     */
    protected array $points = [];

    /**
     * @throws InvalidGeometryException  If the number of points is invalid.
     * @throws CoordinateSystemException If different coordinate systems are used.
     */
    public function __construct(CoordinateSystem $cs, Point ...$points)
    {
        parent::__construct($cs, ! $points);

        if (! $points) {
            return;
        }

        CoordinateSystem::check($this, ...$points);

        $numPoints = count($points);

        if ($numPoints < 3) {
            throw new InvalidGeometryException('A CircularString must be made of at least 3 points.');
        }

        if ($numPoints % 2 === 0) {
            throw new InvalidGeometryException('A CircularString must have an odd number of points.');
        }

        $this->points = array_values($points);
    }

    /**
     * Creates a non-empty CircularString composed of the given points.
     *
     * @param Point    $point1 The first point.
     * @param Point ...$pointN The subsequent points.
     *
     * @throws InvalidGeometryException  If the number of points is invalid.
     * @throws CoordinateSystemException If the points use different coordinate systems.
     */
    public static function of(Point $point1, Point ...$pointN) : CircularString
    {
        return new CircularString($point1->coordinateSystem(), $point1, ...$pointN);
    }

    #[Override]
    public function startPoint() : Point
    {
        if ($this->isEmpty) {
            throw new EmptyGeometryException('The CircularString is empty and has no start point.');
        }

        return $this->points[0];
    }

    #[Override]
    public function endPoint() : Point
    {
        if ($this->isEmpty) {
            throw new EmptyGeometryException('The CircularString is empty and has no end point.');
        }

        return end($this->points);
    }

    /**
     * Returns the number of Points in this CircularString.
     */
    public function numPoints() : int
    {
        return count($this->points);
    }

    /**
     * Returns the specified Point N in this CircularString.
     *
     * @param int $n The point number, 1-based.
     *
     * @throws NoSuchGeometryException If there is no Point at this index.
     */
    public function pointN(int $n) : Point
    {
        if (! isset($this->points[$n - 1])) {
            throw new NoSuchGeometryException('There is no Point in this CircularString at index ' . $n);
        }

        return $this->points[$n - 1];
    }

    /**
     * Returns the points that compose this CircularString.
     *
     * @psalm-return list<Point>
     *
     * @return Point[]
     */
    public function points() : array
    {
        return $this->points;
    }

    #[NoProxy, Override]
    public function geometryType() : string
    {
        return 'CircularString';
    }

    #[NoProxy, Override]
    public function geometryTypeBinary() : int
    {
        return Geometry::CIRCULARSTRING;
    }

    #[Override]
    public function getBoundingBox() : BoundingBox
    {
        $boundingBox = BoundingBox::new();

        foreach ($this->points as $point) {
            $boundingBox = $boundingBox->extendedWithPoint($point);
        }

        return $boundingBox;
    }

    #[Override]
    public function toArray() : array
    {
        return array_map(
            fn (Point $point) => $point->toArray(),
            $this->points,
        );
    }

    #[Override]
    public function project(Projector $projector): CircularString
    {
        return new CircularString(
            $projector->getTargetCoordinateSystem($this->coordinateSystem),
            ...array_map(
                fn (Point $point) => $point->project($projector),
                $this->points,
            ),
        );
    }

    /**
     * Returns the number of points in this CircularString.
     *
     * Required by interface Countable.
     */
    #[Override]
    public function count() : int
    {
        return count($this->points);
    }

    /**
     * Returns an iterator for the points in this CircularString.
     *
     * Required by interface IteratorAggregate.
     *
     * @psalm-return ArrayIterator<int, Point>
     */
    #[Override]
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->points);
    }

    /**
     * Returns a copy of this CircularString, with the given points added.
     */
    public function withAddedPoints(Point ...$points): CircularString
    {
        return new CircularString($this->coordinateSystem, ...$this->points, ...$points);
    }
}
