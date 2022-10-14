<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Exception\UnexpectedGeometryException;
use Brick\Geo\IO\WKTReader;
use Brick\Geo\IO\WKTWriter;
use Brick\Geo\IO\WKBReader;
use Brick\Geo\IO\WKBWriter;

/**
 * Geometry is the root class of the hierarchy.
 */
abstract class Geometry implements \Countable, \IteratorAggregate
{
    public const GEOMETRY           = 0;
    public const POINT              = 1;
    public const LINESTRING         = 2;
    public const POLYGON            = 3;
    public const MULTIPOINT         = 4;
    public const MULTILINESTRING    = 5;
    public const MULTIPOLYGON       = 6;
    public const GEOMETRYCOLLECTION = 7;
    public const CIRCULARSTRING     = 8;
    public const COMPOUNDCURVE      = 9;
    public const CURVEPOLYGON       = 10;
    public const MULTICURVE         = 11;
    public const MULTISURFACE       = 12;
    public const CURVE              = 13;
    public const SURFACE            = 14;
    public const POLYHEDRALSURFACE  = 15;
    public const TIN                = 16;
    public const TRIANGLE           = 17;

    /**
     * The coordinate system of this geometry.
     */
    protected CoordinateSystem $coordinateSystem;

    /**
     * Whether this geometry is empty.
     */
    protected bool $isEmpty;

    /**
     * @param CoordinateSystem $coordinateSystem The coordinate system of this geometry.
     * @param bool             $isEmpty          Whether this geometry is empty.
     */
    protected function __construct(CoordinateSystem $coordinateSystem, bool $isEmpty)
    {
        $this->coordinateSystem = $coordinateSystem;
        $this->isEmpty          = $isEmpty;
    }

    /**
     * Builds a Geometry from a WKT representation.
     *
     * If the resulting geometry is valid but is not an instance of the class this method is called on,
     * for example passing a Polygon WKT to Point::fromText(), an exception is thrown.
     *
     * @param string $wkt  The Well-Known Text representation.
     * @param int    $srid The optional SRID to use.
     *
     * @return static
     *
     * @throws GeometryIOException         If the given string is not a valid WKT representation.
     * @throws CoordinateSystemException   If the WKT contains mixed coordinate systems.
     * @throws InvalidGeometryException    If the WKT represents an invalid geometry.
     * @throws UnexpectedGeometryException If the resulting geometry is not an instance of the current class.
     */
    public static function fromText(string $wkt, int $srid = 0) : Geometry
    {
        /** @var WKTReader|null $wktReader */
        static $wktReader;

        if ($wktReader === null) {
            $wktReader = new WKTReader();
        }

        $geometry = $wktReader->read($wkt, $srid);

        if ($geometry instanceof static) {
            return $geometry;
        }

        throw UnexpectedGeometryException::unexpectedGeometryType(static::class, $geometry);
    }

    /**
     * Builds a Geometry from a WKB representation.
     *
     * If the resulting geometry is valid but is not an instance of the class this method is called on,
     * for example passing a Polygon WKB to Point::fromBinary(), an exception is thrown.
     *
     * @param string $wkb  The Well-Known Binary representation.
     * @param int    $srid The optional SRID to use.
     *
     * @return static
     *
     * @throws GeometryIOException         If the given string is not a valid WKB representation.
     * @throws CoordinateSystemException   If the WKB contains mixed coordinate systems.
     * @throws InvalidGeometryException    If the WKB represents an invalid geometry.
     * @throws UnexpectedGeometryException If the resulting geometry is not an instance of the current class.
     */
    public static function fromBinary(string $wkb, int $srid = 0) : Geometry
    {
        /** @var WKBReader|null $wkbReader */
        static $wkbReader;

        if ($wkbReader === null) {
            $wkbReader = new WKBReader();
        }

        $geometry = $wkbReader->read($wkb, $srid);

        if ($geometry instanceof static) {
            return $geometry;
        }

        throw UnexpectedGeometryException::unexpectedGeometryType(static::class, $geometry);
    }

    /**
     * Returns the inherent dimension of this geometry.
     *
     * This dimension must be less than or equal to the coordinate dimension.
     * In non-homogeneous collections, this will return the largest topological dimension of the contained objects.
     */
    abstract public function dimension() : int;

    /**
     * Returns the coordinate dimension of this geometry.
     *
     * The coordinate dimension is the total number of coordinates in the coordinate system.
     *
     * The coordinate dimension can be 2 (for x and y), 3 (with z or m added), or 4 (with both z and m added).
     * The ordinates x, y and z are spatial, and the ordinate m is a measure.
     */
    public function coordinateDimension() : int
    {
        return $this->coordinateSystem->coordinateDimension();
    }

    /**
     * Returns the spatial dimension of this geometry.
     *
     * The spatial dimension is the number of measurements or axes needed to describe the
     * spatial position of this geometry in a coordinate system.
     *
     * The spatial dimension is 3 if the coordinate system has a Z coordinate, 2 otherwise.
     */
    public function spatialDimension() : int
    {
        return $this->coordinateSystem->spatialDimension();
    }

    /**
     * Returns the name of the instantiable subtype of Geometry of which this Geometry is an instantiable member.
     */
    abstract public function geometryType() : string;

    abstract public function geometryTypeBinary() : int;

    /**
     * Returns the Spatial Reference System ID for this geometry.
     *
     * @noproxy
     *
     * @return int The SRID, zero if not set.
     */
    public function SRID() : int
    {
        return $this->coordinateSystem->SRID();
    }

    /**
     * Returns the WKT representation of this geometry.
     *
     * @noproxy
     */
    public function asText() : string
    {
        /** @var WKTWriter|null $wktWriter */
        static $wktWriter;

        if ($wktWriter === null) {
            $wktWriter = new WKTWriter();
        }

        return $wktWriter->write($this);
    }

    /**
     * Returns the WKB representation of this geometry.
     *
     * @noproxy
     */
    public function asBinary() : string
    {
        /** @var WKBWriter|null $wkbWriter */
        static $wkbWriter;

        if ($wkbWriter === null) {
            $wkbWriter = new WKBWriter();
        }

        return $wkbWriter->write($this);
    }

    /**
     * Returns whether this geometry is the empty Geometry.
     *
     * If true, then this geometry represents the empty point set for the coordinate space.
     */
    public function isEmpty() : bool
    {
        return $this->isEmpty;
    }

    /**
     * Returns whether this geometry has z coordinate values.
     */
    public function is3D() : bool
    {
        return $this->coordinateSystem->hasZ();
    }

    /**
     * Returns whether this geometry has m coordinate values.
     */
    public function isMeasured() : bool
    {
        return $this->coordinateSystem->hasM();
    }

    /**
     * Returns the coordinate system of this geometry.
     */
    public function coordinateSystem() : CoordinateSystem
    {
        return $this->coordinateSystem;
    }

    /**
     * Returns a copy of this Geometry, with the SRID altered.
     *
     * Note that only the SRID value is changed, the coordinates are not reprojected.
     * Use transform() to reproject the Geometry to another SRID.
     *
     * @return static
     */
    public function withSRID(int $srid) : Geometry
    {
        if ($srid === $this->SRID()) {
            return $this;
        }

        $that = clone $this;
        $that->coordinateSystem = $that->coordinateSystem->withSRID($srid);

        return $that;
    }

    /**
     * Returns a copy of this Geometry, with Z and M coordinates removed.
     *
     * @return static
     */
    abstract public function toXY() : Geometry;

    /**
     * Returns a copy of this Geometry, with the Z coordinate removed.
     *
     * @return static
     */
    abstract public function withoutZ() : Geometry;

    /**
     * Returns a copy of this Geometry, with the M coordinate removed.
     *
     * @return static
     */
    abstract public function withoutM() : Geometry;

    /**
     * Returns the bounding box of the Geometry.
     */
    abstract public function getBoundingBox() : BoundingBox;

    /**
     * Returns the raw coordinates of this geometry as an array.
     */
    abstract public function toArray() : array;

    /**
     * Returns a copy of this Geometry, with the X and Y coordinates swapped.
     *
     * @return static
     */
    abstract public function swapXY() : Geometry;

    /**
     * Returns a text representation of this geometry.
     *
     * @noproxy
     */
    final public function __toString() : string
    {
        return $this->asText();
    }
}
