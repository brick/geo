<?php

namespace Brick\Geo;

use Brick\Geo\Engine\GeometryEngineRegistry;
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
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function length()
    {
        return GeometryEngineRegistry::get()->length($this);
    }

    /**
     * {@inheritdoc}
     */
    public function startPoint()
    {
        if ($this->points) {
            return reset($this->points);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function endPoint()
    {
        if ($this->points) {
            return end($this->points);
        }

        return null;
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
     * @throws GeometryException If the specified point does not exist.
     */
    public function pointN($n)
    {
        $i = $n - 1;

        if (! isset($this->points[$i])) {
            throw new GeometryException(sprintf('Point number %d does not exist.', $n));
        }

        return $this->points[$i];
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
     * Returns a nested array representing the coordinates of this LineString.
     *
     * @return array
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
