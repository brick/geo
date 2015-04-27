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
     * @param Point[]               $points The points that compose the LineString.
     * @param CoordinateSystem|null $cs     The coordinate system, optional if the point array is not empty.
     *
     * @return LineString
     *
     * @throws GeometryException
     */
    public static function create(array $points, CoordinateSystem $cs = null)
    {
        $cs = self::checkGeometries($points, Point::class, $cs);

        if ($points && count($points) < 2) {
            throw new GeometryException('A LineString must be made of at least 2 points.');
        }

        $lineString = new LineString($cs, ! $points);
        $lineString->points = array_values($points);

        return $lineString;
    }

    /**
     * Creates a LineString from an array of Points.
     *
     * @deprecated Use create() instead.
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
        return static::create($points);
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
