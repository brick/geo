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
     * Returns a copy of this Point with the X coordinate altered.
     *
     * @param float $x
     *
     * @return Point
     *
     * @throws GeometryException If this point is empty.
     */
    public function withX($x)
    {
        if ($this->isEmpty) {
            throw new GeometryException('Cannot call withX() on an empty Point.');
        }

        $point = clone $this;
        $point->x = (float) $x;

        return $point;
    }

    /**
     * Returns a copy of this Point with the Y coordinate altered.
     *
     * @param float $y
     *
     * @return Point
     *
     * @throws GeometryException If this point is empty.
*/
    public function withY($y)
    {
        if ($this->isEmpty) {
            throw new GeometryException('Cannot call withY() on an empty Point.');
        }

        $point = clone $this;
        $point->y = (float) $y;

        return $point;
    }

    /**
     * Returns a copy of this Point with the Z coordinate altered.
     *
     * @param float $z
     *
     * @return Point
     *
     * @throws GeometryException If this point is empty.
     */
    public function withZ($z)
    {
        if ($this->isEmpty) {
            throw new GeometryException('Cannot call withZ() on an empty Point.');
        }

        $point = clone $this;
        $point->z = (float) $z;
        $point->is3D = true;

        return $point;
    }

    /**
     * Returns a copy of this Point with the M coordinate altered.
     *
     * @param float $m
     *
     * @return Point
     *
     * @throws GeometryException If this point is empty.
     */
    public function withM($m)
    {
        if ($this->isEmpty) {
            throw new GeometryException('Cannot call withZ() on an empty Point.');
        }

        $point = clone $this;
        $point->m = (float) $m;
        $point->isMeasured = true;

        return $point;
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

        $point = clone $this;
        $point->z = null;
        $point->is3D = false;

        return $point;
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

        $point = clone $this;
        $point->m = null;
        $point->isMeasured = false;

        return $point;
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

        $point = clone $this;

        $point->z = null;
        $point->m = null;

        $point->is3D       = false;
        $point->isMeasured = false;

        return $point;
    }

    /**
     * Returns a copy of this Point with the SRID altered.
     *
     * @param integer $srid
     *
     * @return Point
     */
    public function withSRID($srid)
    {
        $point = clone $this;

        $point->srid = (int) $srid;

        return $point;
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
     * Returns an array representing the coordinates of this Point.
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->isEmpty) {
            return [];
        }

        $result = [$this->x, $this->y];

        if ($this->is3D) {
            $result[] = $this->z;
        }

        if ($this->isMeasured) {
            $result[] = $this->m;
        }

        return $result;
    }
}
