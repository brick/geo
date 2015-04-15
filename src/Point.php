<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;

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
     * The x-coordinate value for this Point, or NULL if the point is empty.
     *
     * @var float|null
     */
    private $x;

    /**
     * The y-coordinate value for this Point, or NULL if the point is empty.
     *
     * @var float|null
     */
    private $y;

    /**
     * The z-coordinate value for this Point, or NULL if it does not have one.
     *
     * @var float|null
     */
    private $z;

    /**
     * The m-coordinate value for this Point, or NULL if it does not have one.
     *
     * @var float|null
     */
    private $m;

    /**
     * Creates a point with X and Y coordinates.
     *
     * @param float   $x    The X coordinate.
     * @param float   $y    The Y coordinate.
     * @param integer $srid An optional SRID.
     *
     * @return Point
     */
    public static function xy($x, $y, $srid = 0)
    {
        $point = new Point(false, false, false, (int) $srid);

        $point->x = (float) $x;
        $point->y = (float) $y;

        return $point;
    }

    /**
     * Creates a point with X, Y and Z coordinates.
     *
     * @param float   $x    The X coordinate.
     * @param float   $y    The Y coordinate.
     * @param float   $z    The Z coordinate.
     * @param integer $srid An optional SRID.
     *
     * @return Point
     */
    public static function xyz($x, $y, $z, $srid = 0)
    {
        $point = new Point(false, true, false, (int) $srid);

        $point->x = (float) $x;
        $point->y = (float) $y;
        $point->z = (float) $z;

        return $point;
    }

    /**
     * Creates a point with X, Y and M coordinates.
     *
     * @param float   $x    The X coordinate.
     * @param float   $y    The Y coordinate.
     * @param float   $m    The M coordinate.
     * @param integer $srid An optional SRID.
     *
     * @return Point
     */
    public static function xym($x, $y, $m, $srid = 0)
    {
        $point = new Point(false, false, true, (int) $srid);

        $point->x = (float) $x;
        $point->y = (float) $y;
        $point->m = (float) $m;

        return $point;
    }

    /**
     * Creates a point with X, Y, Z and M coordinates.
     *
     * @param float   $x    The X coordinate.
     * @param float   $y    The Y coordinate.
     * @param float   $z    The Z coordinate.
     * @param float   $m    The M coordinate.
     * @param integer $srid An optional SRID.
     *
     * @return Point
     */
    public static function xyzm($x, $y, $z, $m, $srid = 0)
    {
        $point = new Point(false, true, true, (int) $srid);

        $point->x = (float) $x;
        $point->y = (float) $y;
        $point->z = (float) $z;
        $point->m = (float) $m;

        return $point;
    }

    /**
     * Creates an empty Point with XY dimensionality.
     *
     * @param integer $srid An optional SRID.
     *
     * @return Point
     */
    public static function xyEmpty($srid = 0)
    {
        return new Point(true, false, false, (int) $srid);
    }

    /**
     * Creates an empty Point with XYZ dimensionality.
     *
     * @param integer $srid An optional SRID.
     *
     * @return Point
     */
    public static function xyzEmpty($srid = 0)
    {
        return new Point(true, true, false, (int) $srid);
    }

    /**
     * Creates an empty Point with XYM dimensionality.
     *
     * @param integer $srid An optional SRID.
     *
     * @return Point
     */
    public static function xymEmpty($srid = 0)
    {
        return new Point(true, false, true, (int) $srid);
    }

    /**
     * Creates an empty Point with XYZM dimensionality.
     *
     * @param integer $srid An optional SRID.
     *
     * @return Point
     */
    public static function xyzmEmpty($srid = 0)
    {
        return new Point(true, true, true, (int) $srid);
    }

    /**
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     *
     * @return Point
     */
    public static function pointEmpty($is3D, $isMeasured, $srid)
    {
        return new Point(true, (bool) $is3D, (bool) $isMeasured, (int) $srid);
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
        $point = new Point(false, $z !== null, $m !== null, (int) $srid);

        $point->x = (float) $x;
        $point->y = (float) $y;

        if ($z !== null) {
            $point->z = (float) $z;
        }

        if ($m !== null) {
            $point->m = (float) $m;
        }

        return $point;
    }

    /**
     * Returns the x-coordinate value for this Point.
     *
     * Returns NULL if the Point is empty.
     *
     * @return float|null
     */
    public function x()
    {
        return $this->x;
    }

    /**
     * Returns the y-coordinate value for this Point.
     *
     * Returns NULL if the Point is empty.
     *
     * @return float|null
     */
    public function y()
    {
        return $this->y;
    }

    /**
     * Returns the z-coordinate value for this Point.
     *
     * Returns NULL if the Point is empty, or does not have a Z coordinate.
     *
     * @return float|null
     */
    public function z()
    {
        return $this->z;
    }

    /**
     * Returns the m-coordinate value for this Point.
     *
     * Returns NULL if the Point is empty, or does not have a M coordinate.
     *
     * @return float|null
     */
    public function m()
    {
        return $this->m;
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
     * {@inheritdoc}
     */
    public function toArray()
    {
        if ($this->isEmpty) {
            return [];
        }

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
