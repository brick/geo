<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\EmptyGeometryException;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Exception\NoSuchGeometryException;

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
     * @var Point[]
     */
    protected $points = [];

    /**
     * Class constructor.
     *
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

        $this->points = $points;
    }

    /**
     * Creates a non-empty LineString composed of the given points.
     *
     * @param Point    $point1 The first point.
     * @param Point ...$pointN The subsequent points.
     *
     * @return LineString
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
     * @param Point $a
     * @param Point $b
     *
     * @return LineString
     *
     * @throws CoordinateSystemException If the points use different coordinate systems, or are not 2D.
     */
    public static function rectangle(Point $a, Point $b) : LineString
    {
        $cs = $a->coordinateSystem();

        if ($cs != $b->coordinateSystem()) { // by-value comparison.
            throw CoordinateSystemException::dimensionalityMix($a, $b);
        }

        if ($cs->coordinateDimension() !== 2) {
            throw new CoordinateSystemException(__METHOD__ . ' expects 2D points.');
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

    /**
     * {@inheritdoc}
     */
    public function startPoint() : Point
    {
        if ($this->isEmpty) {
            throw new EmptyGeometryException('The LineString is empty and has no start point.');
        }

        return $this->points[0];
    }

    /**
     * {@inheritdoc}
     */
    public function endPoint() : Point
    {
        if ($this->isEmpty) {
            throw new EmptyGeometryException('The LineString is empty and has no end point.');
        }

        return end($this->points);
    }

    /**
     * Returns the number of Points in this LineString.
     *
     * @return int
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
     * @return Point
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
     * @return Point[]
     */
    public function points() : array
    {
        return $this->points;
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType() : string
    {
        return 'LineString';
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryTypeBinary() : int
    {
        return Geometry::LINESTRING;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        $result = [];

        foreach ($this->points as $point) {
            $result[] = $point->toArray();
        }

        return $result;
    }

    /**
     * Returns the number of points in this LineString.
     *
     * Required by interface Countable.
     *
     * {@inheritdoc}
     */
    public function count() : int
    {
        return count($this->points);
    }

    /**
     * Returns an iterator for the points in this LineString.
     *
     * Required by interface IteratorAggregate.
     *
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->points);
    }
}
