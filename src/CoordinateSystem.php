<?php

namespace Brick\Geo;

use Brick\Geo\Exception\CoordinateSystemException;

/**
 * Represents the dimensionality and spatial reference system of a geometry.
 */
class CoordinateSystem
{
    /**
     * Whether this coordinate system has Z-coordinates.
     *
     * @var boolean
     */
    private $hasZ;

    /**
     * Whether this coordinate system has M-coordinates.
     *
     * @var boolean
     */
    private $hasM;

    /**
     * The Spatial Reference System Identifier of this coordinate system.
     *
     * @var integer
     */
    private $srid;

    /**
     * Class constructor.
     *
     * @param boolean $hasZ Whether the coordinate system has Z-coordinates.
     * @param boolean $hasM Whether the coordinate system has M-coordinates.
     * @param integer $srid The optional Spatial Reference ID of the coordinate system.
     */
    public function __construct($hasZ, $hasM, $srid = 0)
    {
        $this->hasZ = (bool) $hasZ;
        $this->hasM = (bool) $hasM;
        $this->srid = (int)  $srid;
    }

    /**
     * Returns a CoordinateSystem with X and Y coordinates, and an optional SRID.
     *
     * @param integer $srid The optional Spatial Reference ID.
     *
     * @return CoordinateSystem
     */
    public static function xy($srid = 0)
    {
        return new self(false, false, $srid);
    }

    /**
     * Returns a CoordinateSystem with X, Y and Z coordinates, and an optional SRID.
     *
     * @param integer $srid The optional Spatial Reference ID.
     *
     * @return CoordinateSystem
     */
    public static function xyz($srid = 0)
    {
        return new self(true, false, $srid);
    }

    /**
     * Returns a CoordinateSystem with X, Y and M coordinates, and an optional SRID.
     *
     * @param integer $srid The optional Spatial Reference ID.
     *
     * @return CoordinateSystem
     */
    public static function xym($srid = 0)
    {
        return new self(false, true, $srid);
    }


    /**
     * Returns a CoordinateSystem with X, Y, Z and M coordinates, and an optional SRID.
     *
     * @param integer $srid The optional Spatial Reference ID.
     *
     * @return CoordinateSystem
     */
    public static function xyzm($srid = 0)
    {
        return new self(true, true, $srid);
    }

    /**
     * Returns whether this coordinate system has Z-coordinates.
     *
     * @return boolean
     */
    public function hasZ()
    {
        return $this->hasZ;
    }

    /**
     * Returns whether this coordinate system has M-coordinates.
     *
     * @return boolean
     */
    public function hasM()
    {
        return $this->hasM;
    }

    /**
     * Returns the Spatial Reference System Identifier of this coordinate system.
     *
     * @return integer
     */
    public function SRID()
    {
        return $this->srid;
    }

    /**
     * Returns a name for the coordinates in this system, such as XY or XYZ.
     *
     * @return string
     */
    public function coordinateName()
    {
        $name = 'XY';

        if ($this->hasZ) {
            $name .= 'Z';
        }

        if ($this->hasM) {
            $name .= 'M';
        }

        return $name;
    }

    /**
     * Returns the coordinate dimension of this coordinate system.
     *
     * The coordinate dimension is the total number of coordinates in the coordinate system.
     *
     * @return integer 2 for (X,Y), 3 for (X,Y,Z) and (X,Y,M), 4 for (X,Y,Z,M).
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
     * Returns the spatial dimension of this coordinate system.
     *
     * The spatial dimension is 3 if the coordinate system has a Z coordinate, 2 otherwise.
     *
     * @return integer 2 for (X,Y) and (X,Y,M), 3 for (X,Y,Z) and (X,Y,Z,M).
     */
    public function spatialDimension()
    {
        return $this->hasZ ? 3 : 2;
    }

    /**
     * @param Geometry    $reference  The geometry holding the reference coordinate system.
     * @param Geometry ...$geometries The geometries to check against this coordinate system.
     *
     * @return void
     *
     * @throws CoordinateSystemException If the coordinate systems differ.
     */
    public static function check(Geometry $reference, Geometry ...$geometries)
    {
        $referenceCS = $reference->coordinateSystem();

        foreach ($geometries as $geometry) {
            $geometryCS = $geometry->coordinateSystem();

            if ($geometryCS == $referenceCS) { // by-value comparison.
                continue;
            }

            if ($geometryCS->srid !== $referenceCS->srid) {
                throw CoordinateSystemException::sridMix($reference, $geometry);
            }

            throw CoordinateSystemException::dimensionalityMix($reference, $geometry);
        }
    }
}
