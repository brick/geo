<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;

/**
 * A CircularString is a Curve made of zero or more connected circular arc segments.
 *
 * A circular arc segment is a curved segment defined by three points in a two-dimensional plane;
 * the first point cannot be the same as the third point.
 */
class CircularString extends Curve implements \Countable, \IteratorAggregate
{
    /**
     * The Points that compose this CircularString.
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
     * @return CircularString
     *
     * @throws GeometryException
     */
    public static function create(array $points, $is3D, $isMeasured, $srid)
    {
        $is3D       = (bool) $is3D;
        $isMeasured = (bool) $isMeasured;

        $srid = (int) $srid;

        self::checkGeometries($points, Point::class, $is3D, $isMeasured, $srid);

        if ($points) {
            $numPoints = count($points);

            if ($numPoints < 3) {
                throw new GeometryException('A CircularString must be made of at least 3 points.');
            }

            if ($numPoints %2 === 0) {
                throw new GeometryException('A CircularString must have an odd number of points.');
            }
        }

        $circularString = new CircularString(! $points, $is3D, $isMeasured, $srid);
        $circularString->points = array_values($points);

        return $circularString;
    }

    /**
     * {@inheritdoc}
     */
    public function startPoint()
    {
        if ($this->isEmpty) {
            throw new GeometryException('The CircularString is empty and has no start point.');
        }

        return $this->points[0];
    }

    /**
     * {@inheritdoc}
     */
    public function endPoint()
    {
        if ($this->isEmpty) {
            throw new GeometryException('The CircularString is empty and has no end point.');
        }

        return end($this->points);
    }

    /**
     * Returns the number of Points in this CircularString.
     *
     * @return integer
     */
    public function numPoints()
    {
        return count($this->points);
    }

    /**
     * Returns the specified Point N in this CircularString.
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
            throw new GeometryException('There is no Point in this CircularString at index ' . $n);
        }

        return $this->points[$n - 1];
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'CircularString';
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
