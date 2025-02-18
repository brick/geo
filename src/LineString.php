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
 * A LineString is a Curve with linear interpolation between Points.
 *
 * Each consecutive pair of Points defines a line segment.
 */
class LineString extends Curve
{
    /**
     * The Points that compose this LineString.
     *
     * An empty LineString contains no points.
     * A non-empty LineString contains a minimum of 2 points.
     *
     * @psalm-var list<Point>
     *
     * @var Point[]
     */
    protected array $points = [];

    /**
     * A LineString must be composed of 2 points or more, or 0 points for an empty LineString.
     * A LineString with exactly 1 point is not allowed.
     *
     * The coordinate system of each of the points must match the one of the LineString.
     *
     * @param CoordinateSystem $cs        The coordinate system of the LineString.
     * @param Point            ...$points The points that compose the LineString.
     *
     * @throws InvalidGeometryException  If only one point was given.
     * @throws CoordinateSystemException If different coordinate systems are used.
     */
    public function __construct(CoordinateSystem $cs, Point ...$points)
    {
        parent::__construct($cs, ! $points);

        if (! $points) {
            return;
        }

        CoordinateSystem::check($this, ...$points);

        if (count($points) < 2) {
            throw new InvalidGeometryException('A LineString must be composed of at least 2 points.');
        }

        $this->points = array_values($points);
    }

    /**
     * Creates a non-empty LineString composed of the given points.
     *
     * @param Point    $point1 The first point.
     * @param Point ...$pointN The subsequent points.
     *
     * @throws InvalidGeometryException  If only one point was given.
     * @throws CoordinateSystemException If the points use different coordinate systems.
     */
    public static function of(Point $point1, Point ...$pointN) : LineString
    {
        return new LineString($point1->coordinateSystem(), $point1, ...$pointN);
    }

    /**
     * Creates a rectangle out of two 2D corner points.
     *
     * The result is a linear ring (closed and simple).
     *
     * @psalm-suppress PossiblyNullArgument
     *
     * @throws EmptyGeometryException    If any of the points is empty.
     * @throws CoordinateSystemException If the points use different coordinate systems, or are not 2D.
     */
    public static function rectangle(Point $a, Point $b) : LineString
    {
        $cs = $a->coordinateSystem();

        if (! $cs->isEqualTo($b->coordinateSystem())) {
            throw CoordinateSystemException::dimensionalityMix($cs, $b->coordinateSystem());
        }

        if ($cs->coordinateDimension() !== 2) {
            throw new CoordinateSystemException(__METHOD__ . ' expects 2D points.');
        }

        if ($a->isEmpty() || $b->isEmpty()) {
            throw new EmptyGeometryException('Points cannot be empty.');
        }

        $x1 = min($a->x(), $b->x());
        $x2 = max($a->x(), $b->x());

        $y1 = min($a->y(), $b->y());
        $y2 = max($a->y(), $b->y());

        $p1 = new Point($cs, $x1, $y1);
        $p2 = new Point($cs, $x2, $y1);
        $p3 = new Point($cs, $x2, $y2);
        $p4 = new Point($cs, $x1, $y2);

        return new LineString($cs, $p1, $p2, $p3, $p4, $p1);
    }

    #[Override]
    public function startPoint() : Point
    {
        if ($this->isEmpty) {
            throw new EmptyGeometryException('The LineString is empty and has no start point.');
        }

        return $this->points[0];
    }

    #[Override]
    public function endPoint() : Point
    {
        if ($this->isEmpty) {
            throw new EmptyGeometryException('The LineString is empty and has no end point.');
        }

        return end($this->points);
    }

    /**
     * Returns the number of Points in this LineString.
     */
    public function numPoints() : int
    {
        return count($this->points);
    }

    /**
     * Returns the specified Point N in this LineString.
     *
     * @param int $n The point number, 1-based.
     *
     * @throws NoSuchGeometryException If there is no Point at this index.
     */
    public function pointN(int $n) : Point
    {
        if (! isset($this->points[$n - 1])) {
            throw new NoSuchGeometryException('There is no Point in this LineString at index ' . $n);
        }

        return $this->points[$n - 1];
    }

    /**
     * Returns the points that compose this LineString.
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
        return 'LineString';
    }

    #[NoProxy, Override]
    public function geometryTypeBinary() : int
    {
        return Geometry::LINESTRING;
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
    public function project(Projector $projector): LineString
    {
        return new LineString(
            $projector->getTargetCoordinateSystem($this->coordinateSystem),
            ...array_map(
                fn (Point $point) => $point->project($projector),
                $this->points,
            ),
        );
    }

    /**
     * Returns the number of points in this LineString.
     *
     * Required by interface Countable.
     */
    #[Override]
    public function count() : int
    {
        return count($this->points);
    }

    /**
     * Returns an iterator for the points in this LineString.
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
     * Returns a copy of this LineString, with the given points added.
     */
    public function withAddedPoints(Point ...$points): LineString
    {
        return new LineString($this->coordinateSystem, ...$this->points, ...$points);
    }
}
