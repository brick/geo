<?php

namespace Brick\Geo;

/**
 * A Point is a geometry that represents a single location in coordinate space.
 */
class Point extends Geometry
{
    /**
     * The x-coordinate of the Point
     *
     * @var float
     */
    protected $x;

    /**
     * The y-coordinate of the Point
     *
     * @var float
     */
    protected $y;

    /**
     * Class constructor.
     *
     * Internal use only, consumer code must use factory() instead.
     *
     * @param float $x
     * @param float $y
     */
    protected function __construct($x, $y)
    {
        $this->x = (float) $x;
        $this->y = (float) $y;
    }

    /**
     * Factory method to create a new Point from x,y coordinates.
     *
     * @param float $x
     * @param float $y
     *
     * @return Point
     */
    public static function factory($x, $y)
    {
        return new Point($x, $y);
    }

    /**
     * The x-coordinate value for this Point.
     *
     * @return float
     */
    public function x()
    {
        return $this->x;
    }

    /**
     * The y-coordinate value for this Point.
     *
     * @return float
     */
    public function y()
    {
        return $this->y;
    }

    /**
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'Point';
    }

    /**
     * {@inheritdoc}
     *
     * A Point is a 0-dimensional geometric object.
     */
    public function dimension()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(Geometry $geometry)
    {
        return $geometry instanceof Point
            && $geometry->x() == $this->x
            && $geometry->y() == $this->y;
    }
}
