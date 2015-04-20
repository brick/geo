<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;

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
     * @throws GeometryException
     */
    public function __construct(CoordinateSystem $cs, Point ...$points)
    {
        parent::__construct($cs, ! $points);

        if (! $points) {
            return;
        }

        foreach ($points as $point) {
            $cs->checkMatches($point->coordinateSystem());
        }

        if (count($points) < 2) {
            throw new GeometryException('A LineString must be composed of at least 2 points.');
        }

        $this->points = $points;
    }

    /**
     * Returns a LineString composed of the given points.
     *
     * All points must be using the same coordinate system.
     * The coordinate system being inferred from the points, an empty point list is not allowed.
     * To create an empty LineString, use the class constructor instead.
     *
     * @param Point ...$points The points that compose the LineString.
     *
     * @return LineString
     *
     * @throws GeometryException
     */
    public static function of(Point ...$points)
    {
        if (! $points) {
            throw GeometryException::atLeastOneGeometryExpected(static::class, __FUNCTION__);
        }

        return new LineString($points[0]->coordinateSystem(), ...$points);
    }

    /**
     * Creates a LineString from an array of Points.
     *
     * @deprecated Use of() instead.
     *
     * @param Point[] $points
     *
     * @return static The LineString instance.
     *
     * @throws GeometryException If the array contains non-Point objects,
     *                           the result is not of the expected type,
     *                           or dimensionality is mixed.
     */
    public static function factory(array $points)
    {
        return LineString::of(...$points);
    }

    /**
     * {@inheritdoc}
     */
    public function startPoint()
    {
        if ($this->isEmpty) {
            throw new GeometryException('The LineString is empty and has no start point.');
        }

        return $this->points[0];
    }

    /**
     * {@inheritdoc}
     */
    public function endPoint()
    {
        if ($this->isEmpty) {
            throw new GeometryException('The LineString is empty and has no end point.');
        }

        return end($this->points);
    }

    /**
     * Returns the number of Points in this LineString.
     *
     * @return integer
     */
    public function numPoints()
    {
        return count($this->points);
    }

    /**
     * Returns the specified Point N in this LineString.
     *
     * @param integer $n The point number, 1-based.
     *
     * @return Point
     *
     * @throws GeometryException If there is no Point at this index.
     */
    public function pointN($n)
    {
        $n = (int) $n;

        if (! isset($this->points[$n - 1])) {
            throw new GeometryException('There is no Point in this LineString at index ' . $n);
        }

        return $this->points[$n - 1];
    }

    /**
     * Returns the points that compose this LineString.
     *
     * @return Point[]
     */
    public function points()
    {
        return $this->points;
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'LineString';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
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
    public function count()
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
