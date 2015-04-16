<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;

/**
 * Represents the dimensionality and spatial reference system of a geometry.
 */
class CoordinateSystem
{
    /**
     * @var boolean
     */
    private $hasZ;

    /**
     * @var boolean
     */
    private $hasM;

    /**
     * @var integer
     */
    private $srid;

    /**
     * @param boolean $hasZ
     * @param boolean $hasM
     * @param integer $srid
     */
    private function __construct($hasZ, $hasM, $srid)
    {
        $this->hasZ = $hasZ;
        $this->hasM = $hasM;
        $this->srid = $srid;
    }

    /**
     * @param boolean $hasZ
     * @param boolean $hasM
     * @param integer $srid
     *
     * @return CoordinateSystem
     */
    public static function create($hasZ, $hasM, $srid = 0)
    {
        return new self((bool) $hasZ, (bool) $hasM, (int) $srid);
    }

    /**
     * @param integer $srid
     *
     * @return CoordinateSystem
     */
    public static function xy($srid = 0)
    {
        return new self(false, false, (int) $srid);
    }

    /**
     * @param integer $srid
     *
     * @return CoordinateSystem
     */
    public static function xyz($srid = 0)
    {
        return new self(true, false, (int) $srid);
    }

    /**
     * @param integer $srid
     *
     * @return CoordinateSystem
     */
    public static function xym($srid = 0)
    {
        return new self(false, true, (int) $srid);
    }

    /**
     * @param integer $srid
     *
     * @return CoordinateSystem
     */
    public static function xyzm($srid = 0)
    {
        return new self(true, true, (int) $srid);
    }

    /**
     * @return boolean
     */
    public function hasZ()
    {
        return $this->hasZ;
    }

    /**
     * @return boolean
     */
    public function hasM()
    {
        return $this->hasM;
    }

    /**
     * @return integer
     */
    public function SRID()
    {
        return $this->srid;
    }

    /**
     * @return integer
     *
     * @throws GeometryException
     */
    public function coordinateDimension()
    {
        $coordinateDimension = 2;

        if ($this->hasZ) {
            $coordinateDimension++;
        }

        if ($this->hasM) {
            $coordinateDimension++;
        }

        return $coordinateDimension;
    }

    /**
     * @return integer
     */
    public function spatialDimension()
    {
        return $this->hasZ ? 3 : 2;
    }
}
