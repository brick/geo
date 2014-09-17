<?php

namespace Brick\Geo;

/**
 * A Point is a geometry that represents a single location in coordinate space.
 */
class Point extends Geometry
{
    /**
     * The x-coordinate value for this Point.
     *
     * @var float
     */
    protected $x;

    /**
     * The y-coordinate value for this Point.
     *
     * @var float
     */
    protected $y;

    /**
     * The z-coordinate value for this Point, or null if it does not have one.
     *
     * @var float|null
     */
    protected $z;

    /**
     * The m-coordinate value for this Point, or null if it does not have one.
     *
     * @var float|null
     */
    protected $m;

    /**
     * Internal constructor. Use a factory method to obtain an instance.
     *
     * @param float      $x
     * @param float      $y
     * @param float|null $z
     * @param float|null $m
     */
    protected function __construct($x, $y, $z = null, $m = null)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->m = $m;
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
     * Returns the x-coordinate value for this Point.
     *
     * @return float
     */
    public function x()
    {
        return $this->x;
    }

    /**
     * Returns the y-coordinate value for this Point.
     *
     * @return float
     */
    public function y()
    {
        return $this->y;
    }

    /**
     * Returns the z-coordinate value for this Point, if it has one. Returns NULL otherwise.
     *
     * @return float|null
     */
    public function z()
    {
        return $this->x;
    }

    /**
     * Returns the m-coordinate value for this Point, if it has one. Returns NULL otherwise.
     *
     * @return float|null
     */
    public function m()
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
            && $geometry->x === $this->x
            && $geometry->y === $this->y
            && $geometry->z === $this->z
            && $geometry->m === $this->m;
    }
}
