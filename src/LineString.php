<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;

/**
 * A LineString is a Curve with linear interpolation between Points.
 *
 * Each consecutive pair of Points defines a line segment.
 */
class LineString extends Curve implements \Countable, \IteratorAggregate
{
    /**
     * The Points that compose this LineString.
     *
     * @var Point[]
     */
    protected $points = [];

    /**
     * @param Point[] $points
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     *
     * @return LineString
     *
     * @throws GeometryException
     */
    public static function create(array $points, $is3D, $isMeasured, $srid)
    {
        $is3D       = (bool) $is3D;
        $isMeasured = (bool) $isMeasured;

        $srid = (int) $srid;

        self::checkGeometries($points, Point::class, $is3D, $isMeasured, $srid);

        if ($points && count($points) < 2) {
            throw new GeometryException('A LineString must have at least 2 points.');
        }

        $lineString = new LineString(! $points, $is3D, $isMeasured, $srid);
        $lineString->points = array_values($points);

        return $lineString;
    }

    /**
     * Creates a LineString from an array of Points.
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
        foreach ($points as $point) {
            if (! $point instanceof Point) {
                throw GeometryException::unexpectedGeometryType(Point::class, $point);
            }
        }

        self::getDimensions($points, $is3D, $isMeasured, $srid);

        if (count($points) < 2) {
            throw new GeometryException('A LineString must have at least 2 points.');
        }

        $lineString = new LineString(false, $is3D, $isMeasured, $srid);
        $lineString->points = array_values($points);

        return $lineString;
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
     * The point number is 1-based.
     *
     * @param integer $n
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
     * @noproxy
     *
     * {@inheritdoc}
     *
     * A LineString is a 1-dimensional geometric object.
     */
    public function dimension()
    {
        return 1;
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
     * Alias for `numPoints()`, required by interface Countable.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->points);
    }

    /**
     * Returns an iterator for the points, required by interface IteratorAggregate.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->points);
    }
}
