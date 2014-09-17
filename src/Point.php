<?php

namespace Brick\Geo;

/**
 * A Point is a 0-dimensional geometric object and represents a single location in coordinate space.
 *
 * A Point has an x-coordinate value, a y-coordinate value.
 * If called for by the associated Spatial Reference System, it may also have coordinate values for z and m.
 *
 * The boundary of a Point is the empty set.
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
     * @param float      $x The x-coordinate, validated as a float.
     * @param float      $y The y-coordinate, validated as a float.
     * @param float|null $z The z-coordinate, validated as a float or null.
     * @param float|null $m The m-coordinate, validated as a float or null.
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
     * @param float      $x
     * @param float      $y
     * @param float|null $z
     * @param float|null $m
     *
     * @return Point
     */
    public static function factory($x, $y, $z = null, $m = null)
    {
        $x = (float) $x;
        $y = (float) $y;

        if ($z !== null) {
            $z = (float) $z;
        }

        if ($m !== null) {
            $m = (float) $m;
        }

        return new Point($x, $y, $z, $m);
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
        return $this->z;
    }

    /**
     * Returns the m-coordinate value for this Point, if it has one. Returns NULL otherwise.
     *
     * @return float|null
     */
    public function m()
    {
        return $this->m;
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
