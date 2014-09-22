<?php

namespace Brick\Geo;

use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Exception\GeometryException;
use Brick\Geo\IO\WktReader;
use Brick\Geo\IO\WktWriter;
use Brick\Geo\IO\WkbReader;
use Brick\Geo\IO\WkbWriter;

/**
 * Geometry is the root class of the hierarchy.
 */
abstract class Geometry
{
    const GEOMETRY           = 0;
    const POINT              = 1;
    const LINESTRING         = 2;
    const POLYGON            = 3;
    const MULTIPOINT         = 4;
    const MULTILINESTRING    = 5;
    const MULTIPOLYGON       = 6;
    const GEOMETRYCOLLECTION = 7;
    const CIRCULARSTRING     = 8;
    const COMPOUNDCURVE      = 9;
    const CURVEPOLYGON       = 10;
    const MULTICURVE         = 11;
    const MULTISURFACE       = 12;
    const CURVE              = 13;
    const SURFACE            = 14;
    const POLYHEDRALSURFACE  = 15;
    const TIN                = 16;
    const TRIANGLE           = 17;

    /**
     * Default SRID for Geometries;
     * This library assumes that all Geometries are in WGS84 Lon/Lat.
     *
     * @const integer
     */
    const WGS84 = 4326;

    /**
     * Builds a Geometry from a WKT representation.
     *
     * @param string $wkt The Well-Known Text representation.
     *
     * @return static
     *
     * @throws GeometryException If the geometry is not of this type.
     */
    public static function fromText($wkt)
    {
        $geometry = WktReader::read($wkt);

        if (! $geometry instanceof static) {
            throw GeometryException::unexpectedGeometryType(static::class, $geometry);
        }

        return $geometry;
    }

    /**
     * Builds a Geometry from a WKB representation.
     *
     * @param string $wkb The Well-Known Binary representation.
     *
     * @return static
     *
     * @throws GeometryException If the geometry is not of this type.
     */
    final public static function fromBinary($wkb)
    {
        $geometry = WkbReader::read($wkb);

        if (! $geometry instanceof static) {
            throw GeometryException::unexpectedGeometryType(static::class, $geometry);
        }

        return $geometry;
    }

    /**
     * Returns the inherent dimension of this geometric object.
     *
     * This dimension must be less than or equal to the coordinate dimension.
     * In non-homogeneous collections, this will return the largest topological dimension of the contained objects.
     *
     * @return integer
     */
    abstract public function dimension();

    /**
     * Returns the coordinate dimension of this Geometry.
     *
     * @return integer
     *
     * @throws GeometryException
     */
    public function coordinateDimension()
    {
        throw GeometryException::unimplementedMethod(__METHOD__);
    }

    /**
     * Returns the spatial dimension of this Geometry.
     *
     * @return integer
     *
     * @throws GeometryException
     */
    public function spatialDimension()
    {
        throw GeometryException::unimplementedMethod(__METHOD__);
    }

    /**
     * Returns the name of the instantiable subtype of Geometry of which this Geometry is an instantiable member.
     *
     * @return string
     */
    abstract public function geometryType();

    /**
     * Returns the Spatial Reference System ID for this geometric object.
     *
     * @todo only WGS84 is supported right now.
     *
     * @return integer
     */
    public function SRID()
    {
        return self::WGS84;
    }

    /**
     * Returns the minimum bounding box for this Geometry.
     *
     * The polygon is defined by the corner points of the bounding box
     * [(MINX, MINY), (MAXX, MINY), (MAXX, MAXY), (MINX, MAXY), (MINX, MINY)].
     * Minimums for Z and M may be added. The simplest representation of an Envelope
     * is as two direct positions, one containing all the minimums, and another all
     * the maximums. In some cases, this coordinate will be outside the range of
     * validity for the Spatial Reference System.
     *
     * @return Geometry
     */
    public function envelope()
    {
        return GeometryEngineRegistry::get()->envelope($this);
    }

    /**
     * Returns the WKT representation of this Geometry.
     *
     * @return string
     */
    final public function asText()
    {
        return WktWriter::write($this);
    }

    /**
     * Returns the WKB representation of this Geometry.
     *
     * @return string
     */
    final public function asBinary()
    {
        return WkbWriter::write($this);
    }

    /**
     * Returns true if this geometric object is the empty Geometry.
     *
     * If true, then this geometric object represents the empty point set for the coordinate space.
     *
     * @return boolean
     */
    abstract public function isEmpty();

    /**
     * Returns whether this Geometry is simple/
     *
     * Returns true if this geometric object has no anomalous geometric points,
     * such as self intersection or self tangency. The description of each
     * instantiable geometric class will include the specific conditions that
     * cause an instance of that class to be classified as not simple.
     * Implemented using a GeometryEngine.
     *
     * @todo implement this method in PHP, to avoid a database round trip when creating LinearRing, Polygon, and so on.
     *
     * @return boolean
     */
    public function isSimple()
    {
        return GeometryEngineRegistry::get()->isSimple($this);
    }

    /**
     * Returns true if this geometric object has z coordinate values.
     *
     * @todo This should be abstract: each subclass should implement it.
     *
     * @return boolean
     */
    public function is3D()
    {
        return false;
    }

    /**
     * Returns true if this geometric object has m coordinate values.
     *
     * @todo This should be abstract: each subclass should implement it.
     *
     * @return boolean
     */
    public function isMeasured()
    {
        return false;
    }

    /**
     * Returns the closure of the combinatorial boundary of this geometric object.
     *
     * Because the result of this function is a closure, and hence topologically closed,
     * the resulting boundary can be represented using representational Geometry primitives.
     *
     * @return Geometry
     */
    public function boundary()
    {
        return GeometryEngineRegistry::get()->boundary($this);
    }

    /**
     * Returns true if this geometric object is "spatially equal" to $geometry.
     *
     * @param Geometry $geometry
     *
     * @return boolean
     */
    public function equals(Geometry $geometry)
    {
        if ($this->isEmpty() and $geometry->isEmpty()) {
            return true;
        }

        if ($this->isEmpty() xor $geometry->isEmpty()) {
            return false;
        }

        return GeometryEngineRegistry::get()->equals($this, $geometry);
    }

    /**
     * Returns true if this geometric object is "spatially disjoint" from $geometry.
     *
     * @param Geometry $geometry
     *
     * @return boolean
     */
    public function disjoint(Geometry $geometry)
    {
        return GeometryEngineRegistry::get()->disjoint($this, $geometry);
    }

    /**
     * Returns true if this geometric object "spatially intersects" $geometry.
     *
     * @param Geometry $geometry
     *
     * @return boolean
     */
    public function intersects(Geometry $geometry)
    {
        if ($this->isEmpty() or $geometry->isEmpty()) {
            return false;
        }

        return GeometryEngineRegistry::get()->intersects($this, $geometry);
    }

    /**
     * Returns true if this geometric object "spatially touches" $geometry.
     *
     * @param Geometry $geometry
     *
     * @return boolean
     */
    public function touches(Geometry $geometry)
    {
        return GeometryEngineRegistry::get()->touches($this, $geometry);
    }

    /**
     * Returns true if this geometric object "spatially crosses" $geometry.
     *
     * @param Geometry $geometry
     *
     * @return boolean
     */
    public function crosses(Geometry $geometry)
    {
        return GeometryEngineRegistry::get()->crosses($this, $geometry);
    }

    /**
     * Returns true if this geometric object is "spatially within" $geometry.
     *
     * @param Geometry $geometry
     *
     * @return boolean
     */
    public function within(Geometry $geometry)
    {
        return GeometryEngineRegistry::get()->within($this, $geometry);
    }

    /**
     * Returns true if this geometric object "spatially contains" $geometry.
     *
     * @param Geometry $geometry
     *
     * @return boolean
     */
    public function contains(Geometry $geometry)
    {
        if ($this->isEmpty() or $geometry->isEmpty()) {
            return false;
        }

        return GeometryEngineRegistry::get()->contains($this, $geometry);
    }

    /**
     * Returns true if this geometric object "spatially overlaps" $geometry.
     *
     * @param Geometry $geometry
     *
     * @return boolean
     */
    public function overlaps(Geometry $geometry)
    {
        return GeometryEngineRegistry::get()->overlaps($this, $geometry);
    }

    /**
     * Returns true if this geometric object is spatially related to $geometry
     * by testing for intersections between the interior, boundary and exterior of the
     * two geometric objects as specified by the values in the intersectionPatternMatrix.
     * This returns false if all the tested intersections are empty except
     * exterior (this) intersect exterior (geometry).
     *
     * @param Geometry $geometry
     * @param string   $matrix
     *
     * @return boolean
     */
    public function relate(Geometry $geometry, $matrix)
    {
        return GeometryEngineRegistry::get()->relate($this, $geometry, $matrix);
    }

    /**
     * Returns a derived geometry collection value that matches the specified m coordinate value.
     *
     * @param float $mValue
     *
     * @return Geometry
     */
    public function locateAlong($mValue)
    {
        return GeometryEngineRegistry::get()->locateAlong($this, $mValue);
    }

    /**
     * Returns a derived geometry collection value that matches the specified range of m coordinate values inclusively.
     *
     * @param float $mStart
     * @param float $mEnd
     *
     * @return Geometry
     */
    public function locateBetween($mStart, $mEnd)
    {
        return GeometryEngineRegistry::get()->locateBetween($this, $mStart, $mEnd);
    }

    /**
     * Returns the shortest distance between any two Points in the two geometric objects.
     *
     * The distance is calculated in the spatial reference system of
     * this geometric object. Because the geometries are closed, it is
     * possible to find a point on each geometric object involved, such
     * that the distance between these 2 points is the returned distance
     * between their geometric objects.
     *
     * @param Geometry $geometry
     *
     * @return float
     */
    public function distance(Geometry $geometry)
    {
        return GeometryEngineRegistry::get()->distance($this, $geometry);
    }

    /**
     * Returns a geometric object that represents all Points whose distance
     * from this geometric object is less than or equal to distance.
     * Calculations are in the spatial reference system of this geometric object.
     * Because of the limitations of linear interpolation, there will often be
     * some relatively small error in this distance, but it should be near the
     * resolution of the coordinates used.
     *
     * @param float $distance
     *
     * @return Geometry
     */
    public function buffer($distance)
    {
        return GeometryEngineRegistry::get()->buffer($this, $distance);
    }

    /**
     * Returns a geometric object that represents the convex hull of this geometric object.
     *
     * Convex hulls, being dependent on straight lines,
     * can be accurately represented in linear interpolations for any
     * geometry restricted to linear interpolations.
     *
     * @return Geometry
     */
    public function convexHull()
    {
        return GeometryEngineRegistry::get()->convexHull($this);
    }

    /**
     * Returns a geometric object that represents the Point set intersection of this geometric object with `$geometry`.
     *
     * @param Geometry $geometry
     *
     * @return Geometry
     */
    public function intersection(Geometry $geometry)
    {
        return GeometryEngineRegistry::get()->intersection($this, $geometry);
    }

    /**
     * Returns a geometric object that represents the Point set union of this geometric object with `$geometry`.
     *
     * @param Geometry $geometry
     *
     * @return Geometry
     */
    public function union(Geometry $geometry)
    {
        return GeometryEngineRegistry::get()->union($this, $geometry);
    }

    /**
     * Returns a geometric object that represents the Point set difference of this geometric object with `$geometry`.
     *
     * @param Geometry $geometry
     *
     * @return Geometry
     */
    public function difference(Geometry $geometry)
    {
        return GeometryEngineRegistry::get()->difference($this, $geometry);
    }

    /**
     * Returns a geometric object that represents the Point set symmetric difference of this Geometry with `$geometry`.
     *
     * @param Geometry $geometry
     *
     * @return Geometry
     */
    public function symDifference(Geometry $geometry)
    {
        return GeometryEngineRegistry::get()->symDifference($this, $geometry);
    }

    /**
     * Returns a text representation of this Geometry.
     *
     * @return string
     */
    final public function __toString()
    {
        return $this->asText();
    }
}
