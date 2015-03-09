<?php

namespace Brick\Geo;

use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Exception\GeometryException;
use Brick\Geo\IO\WKTReader;
use Brick\Geo\IO\WKTWriter;
use Brick\Geo\IO\WKBReader;
use Brick\Geo\IO\WKBWriter;

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
     * The Spatial Reference System ID for this geometric object.
     *
     * The SRID is zero if not set.
     *
     * @var integer
     */
    protected $srid = 0;

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
        $geometry = (new WKTReader())->read($wkt);

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
    public static function fromBinary($wkb)
    {
        $geometry = (new WKBReader())->read($wkb);

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
     *
     * @throws GeometryException If the geometry is empty.
     */
    abstract public function dimension();

    /**
     * Returns the coordinate dimension of this Geometry.
     *
     * The coordinate dimension can be 2 (for x and y), 3 (with z or m added), or 4 (with both z and m added).
     * The ordinates x, y and z are spatial, and the ordinate m is a measure.
     *
     * @noproxy
     *
     * @return integer
     *
     * @throws GeometryException
     */
    public function coordinateDimension()
    {
        $coordinateDimension = 2;

        if ($this->is3D()) {
            $coordinateDimension++;
        }

        if ($this->isMeasured()) {
            $coordinateDimension++;
        }

        return $coordinateDimension;
    }

    /**
     * Returns the spatial dimension of this Geometry.
     *
     * The spatial dimension is the number of measurements or axes needed to describe the
     * spatial position of this geometry in a coordinate system.
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
     * @return integer The SRID, zero if not set.
     */
    public function SRID()
    {
        return $this->srid;
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
     * @noproxy
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
     * @noproxy
     *
     * @return string
     */
    public function asText()
    {
        return (new WKTWriter())->write($this);
    }

    /**
     * Returns the WKB representation of this Geometry.
     *
     * @noproxy
     *
     * @return string
     */
    public function asBinary()
    {
        return (new WKBWriter())->write($this);
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
     * @todo implement this method in PHP, to avoid an engine call when creating LinearRing, Polygon, and so on.
     *
     * @noproxy
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
     * @return boolean
     */
    abstract public function is3D();

    /**
     * Returns true if this geometric object has m coordinate values.
     *
     * @return boolean
     */
    abstract public function isMeasured();

    /**
     * Returns the closure of the combinatorial boundary of this geometric object.
     *
     * Because the result of this function is a closure, and hence topologically closed,
     * the resulting boundary can be represented using representational Geometry primitives.
     *
     * @noproxy
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
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return boolean
     */
    public function equals(Geometry $geometry)
    {
        return GeometryEngineRegistry::get()->equals($this, $geometry);
    }

    /**
     * Returns true if this geometric object is "spatially disjoint" from $geometry.
     *
     * @noproxy
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
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return boolean
     */
    public function intersects(Geometry $geometry)
    {
        return GeometryEngineRegistry::get()->intersects($this, $geometry);
    }

    /**
     * Returns true if this geometric object "spatially touches" $geometry.
     *
     * @noproxy
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
     * @noproxy
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
     * @noproxy
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
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return boolean
     */
    public function contains(Geometry $geometry)
    {
        return GeometryEngineRegistry::get()->contains($this, $geometry);
    }

    /**
     * Returns true if this geometric object "spatially overlaps" $geometry.
     *
     * @noproxy
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
     * @noproxy
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
     * @noproxy
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
     * @noproxy
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
     * @noproxy
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
     * @noproxy
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
     * @noproxy
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
     * @noproxy
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
     * @noproxy
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
     * @noproxy
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
     * @noproxy
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
     * Snap all points of the input geometry to a regular grid.
     *
     * @noproxy
     *
     * @param $size
     *
     * @return Geometry
     */
    public function snapToGrid($size)
    {
        return GeometryEngineRegistry::get()->snapToGrid($this, $size);
    }

    /**
     * Returns a "simplified" version of the given geometry using the Douglas-Peucker algorithm.
     *
     * @noproxy
     *
     * @param $tolerance
     *
     * @return Geometry
     */
    public function simplify($tolerance)
    {
        return GeometryEngineRegistry::get()->simplify($this, $tolerance);
    }

    /**
     * Returns the 2-dimensional largest distance between two geometries in projected units.
     *
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return float
     */
    public function maxDistance(Geometry $geometry)
    {
        return GeometryEngineRegistry::get()->maxDistance($this, $geometry);
    }

    /**
     * Returns the raw coordinates of this Geometry as an array.
     *
     * @return array
     */
    abstract public function toArray();

    /**
     * Returns a text representation of this Geometry.
     *
     * @noproxy
     *
     * @return string
     */
    final public function __toString()
    {
        return $this->asText();
    }

    /**
     * Gets the dimensions of an array of geometries.
     *
     * If dimensionality is mixed, an exception is thrown.
     *
     * @internal
     *
     * @param Geometry[] $geometries The geometries, validated as such.
     * @param boolean    $is3D       A variable to store whether the geometries have Z coordinates.
     * @param boolean    $isMeasured A variable to store whether the geometries have M coordinates.
     * @param integer    $srid       A variable to store the SRID of the geometries.
     *
     * @return void
     *
     * @throws GeometryException If dimensionality is mixed.
     */
    protected static function getDimensions(array $geometries, & $is3D, & $isMeasured, & $srid)
    {
        $is3D       = false;
        $isMeasured = false;
        $srid       = 0;

        $previous = null;

        foreach ($geometries as $geometry) {
            if ($previous === null) {
                $is3D       = $geometry->is3D();
                $isMeasured = $geometry->isMeasured();
                $srid       = $geometry->SRID();
                $previous   = $geometry;
            } else {
                if ($geometry->is3D() !== $is3D || $geometry->isMeasured() !== $isMeasured || $geometry->SRID() !== $srid) {
                    throw GeometryException::dimensionalityMix($previous, $geometry);
                }
            }
        }
    }
}
