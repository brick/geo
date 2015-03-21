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
     * @param float      $x    The x-coordinate, validated as a float.
     * @param float      $y    The y-coordinate, validated as a float.
     * @param float|null $z    The z-coordinate, validated as a float or null.
     * @param float|null $m    The m-coordinate, validated as a float or null.
     * @param integer    $srid The SRID, validated as an integer.
     */
    protected function __construct($x, $y, $z, $m, $srid)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->m = $m;

        $this->srid = $srid;
    }

    /**
     * Creates a point with X and Y coordinates.
     *
     * @param float $x    The X coordinate.
     * @param float $y    The Y coordinate.
     * @param int   $srid The SRID, optional.
     *
     * @return Point
     */
    public static function xy($x, $y, $srid = 0)
    {
        return new Point((float) $x, (float) $y, null, null, (int) $srid);
    }

    /**
     * Creates a point with X, Y and Z coordinates.
     *
     * @param float $x    The X coordinate.
     * @param float $y    The Y coordinate.
     * @param float $z    The Z coordinate.
     * @param int   $srid The SRID, optional.
     *
     * @return Point
     */
    public static function xyz($x, $y, $z, $srid = 0)
    {
        return new Point((float) $x, (float) $y, (float) $z, null, (int) $srid);
    }

    /**
     * Creates a point with X, Y and M coordinates.
     *
     * @param float $x    The X coordinate.
     * @param float $y    The Y coordinate.
     * @param float $m    The M coordinate.
     * @param int   $srid The SRID, optional.
     *
     * @return Point
     */
    public static function xym($x, $y, $m, $srid = 0)
    {
        return new Point((float) $x, (float) $y, null, (float) $m, (int) $srid);
    }

    /**
     * Creates a point with X, Y, Z and M coordinates.
     *
     * @param float $x    The X coordinate.
     * @param float $y    The Y coordinate.
     * @param float $z    The Z coordinate.
     * @param float $m    The M coordinate.
     * @param int   $srid The SRID, optional.
     *
     * @return Point
     */
    public static function xyzm($x, $y, $z, $m, $srid = 0)
    {
        return new Point((float) $x, (float) $y, (float) $z, (float) $m, (int) $srid);
    }

    /**
     * Factory method to create a new Point.
     *
     * Deprecated in favor of xy(), xyz(), xym() and xyzm() factory methods.
     *
     * @deprecated
     *
     * @param float      $x    The x-coordinate.
     * @param float      $y    The y-coordinate.
     * @param float|null $z    The z-coordinate, optional.
     * @param float|null $m    The m-coordinate, optional.
     * @param integer    $srid The SRID, optional.
     *
     * @return Point
     */
    public static function factory($x, $y, $z = null, $m = null, $srid = 0)
    {
        $x = (float) $x;
        $y = (float) $y;

        if ($z !== null) {
            $z = (float) $z;
        }

        if ($m !== null) {
            $m = (float) $m;
        }

        $srid = (int) $srid;

        return new Point($x, $y, $z, $m, $srid);
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
     * Returns a copy of this Point with the X coordinate altered.
     *
     * @param float $x
     *
     * @return Point
     */
    public function withX($x)
    {
        return new Point((float) $x, $this->y, $this->z, $this->m, $this->srid);
    }

    /**
     * Returns a copy of this Point with the Y coordinate altered.
     *
     * @param float $y
     *
     * @return Point
     */
    public function withY($y)
    {
        return new Point($this->x, (float) $y, $this->z, $this->m, $this->srid);
    }

    /**
     * Returns a copy of this Point with the Z coordinate altered.
     *
     * @param float $z
     *
     * @return Point
     */
    public function withZ($z)
    {
        return new Point($this->x, $this->y, (float) $z, $this->m, $this->srid);
    }

    /**
     * Returns a copy of this Point with the M coordinate altered.
     *
     * @param float $m
     *
     * @return Point
     */
    public function withM($m)
    {
        return new Point($this->x, $this->y, $this->z, (float) $m, $this->srid);
    }

    /**
     * Returns a copy of this Point with the Z coordinate removed.
     *
     * @return Point
     */
    public function withoutZ()
    {
        if ($this->z === null) {
            return $this;
        }

        return new Point($this->x, $this->y, null, $this->m, $this->srid);
    }

    /**
     * Returns a copy of this Point with the M coordinate removed.
     *
     * @return Point
     */
    public function withoutM()
    {
        if ($this->m === null) {
            return $this;
        }

        return new Point($this->x, $this->y, $this->z, null, $this->srid);
    }

    /**
     * Returns a copy of this Point with the Z and M coordinates removed.
     *
     * @return Point
     */
    public function withoutZM()
    {
        if ($this->z === null && $this->m === null) {
            return $this;
        }

        return new Point($this->x, $this->y, null, null, $this->srid);
    }

    /**
     * Returns a copy of this Point with the SRID altered.
     *
     * @param int $srid
     *
     * @return Point
     */
    public function withSRID($srid)
    {
        return new Point($this->x, $this->y, $this->z, $this->m, (int) $srid);
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'Point';
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function dimension()
    {
        return 0;
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function envelope()
    {
        return $this->withoutZM();
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function boundary()
    {
        return GeometryCollection::xy([], $this->srid);
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function isSimple()
    {
        return true;
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
     * {@inheritdoc}
     */
    public function is3D()
    {
        return $this->z !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function isMeasured()
    {
        return $this->m !== null;
    }

    /**
     * Returns an array representing the coordinates of this Point.
     *
     * @return array
     */
    public function toArray()
    {
        $result = [$this->x, $this->y];

        if ($this->z !== null) {
            $result[] = $this->z;
        }

        if ($this->m !== null) {
            $result[] = $this->m;
        }

        return $result;
    }
}
