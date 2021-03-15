<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Exception\CoordinateSystemException;

/**
 * Represents the dimensionality and spatial reference system of a geometry.
 *
 * This class is immutable.
 */
class CoordinateSystem
{
    /**
     * Whether this coordinate system has Z-coordinates.
     */
    private bool $hasZ;

    /**
     * Whether this coordinate system has M-coordinates.
     */
    private bool $hasM;

    /**
     * The Spatial Reference System Identifier of this coordinate system.
     */
    private int $srid;

    /**
     * @param bool $hasZ Whether the coordinate system has Z-coordinates.
     * @param bool $hasM Whether the coordinate system has M-coordinates.
     * @param int  $srid The optional Spatial Reference ID of the coordinate system.
     */
    public function __construct(bool $hasZ, bool $hasM, int $srid = 0)
    {
        $this->hasZ = $hasZ;
        $this->hasM = $hasM;
        $this->srid = $srid;
    }

    /**
     * Returns a CoordinateSystem with X and Y coordinates, and an optional SRID.
     */
    public static function xy(int $srid = 0) : CoordinateSystem
    {
        return new self(false, false, $srid);
    }

    /**
     * Returns a CoordinateSystem with X, Y and Z coordinates, and an optional SRID.
     */
    public static function xyz(int $srid = 0) : CoordinateSystem
    {
        return new self(true, false, $srid);
    }

    /**
     * Returns a CoordinateSystem with X, Y and M coordinates, and an optional SRID.
     */
    public static function xym(int $srid = 0) : CoordinateSystem
    {
        return new self(false, true, $srid);
    }

    /**
     * Returns a CoordinateSystem with X, Y, Z and M coordinates, and an optional SRID.
     */
    public static function xyzm(int $srid = 0) : CoordinateSystem
    {
        return new self(true, true, $srid);
    }

    /**
     * Returns whether this coordinate system has Z-coordinates.
     */
    public function hasZ() : bool
    {
        return $this->hasZ;
    }

    /**
     * Returns whether this coordinate system has M-coordinates.
     */
    public function hasM() : bool
    {
        return $this->hasM;
    }

    /**
     * Returns the Spatial Reference System Identifier of this coordinate system.
     */
    public function SRID() : int
    {
        return $this->srid;
    }

    /**
     * Returns a name for the coordinates in this system, such as XY or XYZ.
     */
    public function coordinateName() : string
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
     * @return int 2 for (X,Y), 3 for (X,Y,Z) and (X,Y,M), 4 for (X,Y,Z,M).
     */
    public function coordinateDimension() : int
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
     * @return int 2 for (X,Y) and (X,Y,M), 3 for (X,Y,Z) and (X,Y,Z,M).
     */
    public function spatialDimension() : int
    {
        return $this->hasZ ? 3 : 2;
    }

    /**
     * Returns a copy of this CoordinateSystem with the $hasZ altered.
     */
    public function withZ(bool $hasZ) : CoordinateSystem
    {
        if ($hasZ === $this->hasZ) {
            return $this;
        }

        $that = clone $this;
        $that->hasZ = $hasZ;

        return $that;
    }

    /**
     * Returns a copy of this CoordinateSystem with the $hasM altered.
     */
    public function withM(bool $hasM) : CoordinateSystem
    {
        if ($hasM === $this->hasM) {
            return $this;
        }

        $that = clone $this;
        $that->hasM = $hasM;

        return $that;
    }

    /**
     * Returns a copy of this CoordinateSystem with the SRID altered.
     */
    public function withSRID(int $srid) : CoordinateSystem
    {
        if ($srid === $this->srid) {
            return $this;
        }

        $that = clone $this;
        $that->srid = $srid;

        return $that;
    }

    public function isEqualTo(CoordinateSystem $that) : bool
    {
        return $this->hasZ === $that->hasZ
            && $this->hasM === $that->hasM
            && $this->srid === $that->srid;
    }

    /**
     * @param Geometry    $reference  The geometry holding the reference coordinate system.
     * @param Geometry ...$geometries The geometries to check against this coordinate system.
     *
     * @throws CoordinateSystemException If the coordinate systems differ.
     */
    public static function check(Geometry $reference, Geometry ...$geometries) : void
    {
        $referenceCS = $reference->coordinateSystem();

        foreach ($geometries as $geometry) {
            $geometryCS = $geometry->coordinateSystem();

            if ($geometryCS->isEqualTo($referenceCS)) {
                continue;
            }

            if ($geometryCS->srid !== $referenceCS->srid) {
                throw CoordinateSystemException::sridCompositionMix($reference, $geometry);
            }

            throw CoordinateSystemException::dimensionalityCompositionMix($reference, $geometry);
        }
    }
}
