<?php

namespace Brick\Geo;

use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Exception\GeometryException;

/**
 * A LineString is a Curve with linear interpolation between Points.
 *
 * Each consecutive pair of Points defines a Line segment.
 */
class LineString extends Curve implements \Countable, \IteratorAggregate
{
    /**
     * An array of Point objects.
     *
     * @var Point[]
     */
    protected $points = [];

    /**
     * Internal constructor. Use a factory method to obtain an instance.
     *
     * @param Point[] $points The points, validated.
     */
    protected function __construct(array $points)
    {
        $this->points = $points;
    }

    /**
     * Creates a LineString from an array of Points.
     *
     * The result can be a subclass of LineString, such as Line or LinearRing.
     *
     * @param Point[] $points
     *
     * @return static The LineString instance.
     *
     * @throws GeometryException If the array contains non-Point objects, or if the result is not of the expected type.
     */
    public static function factory(array $points)
    {
        foreach ($points as $point) {
            if (! $point instanceof Point) {
                throw GeometryException::unexpectedGeometryType(Point::class, $point);
            }
        }

        /** @var Point[] $points */
        $points = array_values($points);
        $count = count($points);

        if ($count < 2) {
            throw new GeometryException('A LineString must have at least 2 points.');
        }

        if ($count === 2) {
            $result = new Line($points);
        } elseif ($points[0]->equals($points[$count - 1])) {
            $result = new LinearRing($points);
        } else {
            $result = new LineString($points);
        }

        if (! $result instanceof static) {
            throw GeometryException::unexpectedGeometryType(static::class, $result);
        }

        return $result;
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
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function isClosed()
    {
        return $this->startPoint()->equals($this->endPoint());
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function isRing()
    {
        return $this->isClosed() && $this->isSimple();
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
            throw new GeometryException(sprintf('Point number %d is not in range 1 to %d.', $n, $this->count()));
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
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return false;
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
