<?php

namespace Brick\Geo;

/**
 * A LineString is a Curve with linear interpolation between points.
 */
class LineString extends Curve implements \Countable, \IteratorAggregate
{
    /**
     * An array of Point objects.
     *
     * @var array
     */
    protected $points = [];

    /**
     * Class constructor.
     *
     * Internal use only, consumer code must use factory() instead
     *
     * @param \Brick\Geo\Point[] $points
     */
    protected function __construct(array $points)
    {
        foreach ($points as $point) {
            $this->addPoint($point);
        }
    }

    /**
     * Internal method for the constructor, to provide strong typing.
     *
     * @param Point $point
     */
    private function addPoint(Point $point)
    {
        $this->points[] = $point;
    }

    /**
     * @param Point[] $points
     *
     * @return LineString
     *
     * @throws GeometryException
     */
    public static function factory(array $points)
    {
        if (count($points) < 2) {
            throw new GeometryException('A LineString must have at least 2 points');
        }

        if (count($points) == 2) {
            return new Line($points);
        }

        if (reset($points)->equals(end($points))) {
            return new LinearRing($points);
        }

        return new LineString($points);
    }

    /**
     * {@inheritdoc}
     *
     * Implemented using a GeometryService.
     */
    public function length()
    {
        return self::getService()->length($this);
    }

    /**
     * {@inheritdoc}
     */
    public function startPoint()
    {
        return reset($this->points);
    }

    /**
     * {@inheritdoc}
     */
    public function endPoint()
    {
        return end($this->points);
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed()
    {
        return $this->startPoint()->equals($this->endPoint());
    }

    /**
     * {@inheritdoc}
     */
    public function isRing()
    {
        return $this->isClosed() && $this->isSimple();
    }

    /**
     * The number of Points in this LineString.
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
     * @throws GeometryException
     */
    public function pointN($n)
    {
        if (! is_int($n)) {
            throw new GeometryException('The point number must be an integer');
        }

        // decrement the index, as our array is 0-based
        $n--;

        if ($n < 0 || $n >= count($this->points)) {
            throw new GeometryException('Point number out of range');
        }

        return $this->points[$n];
    }

    /**
     * {@inheritdoc}
     *
     * A LineString is a 1-dimensional geometric object.
     */
    public function dimension()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'LineString';
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return false;
    }

    /**
     * Returns the total number of points in this LineString.
     *
     * Required by interface Countable.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->points);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->points);
    }
}
