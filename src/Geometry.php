<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\GeometryEngineException;
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
     *
     * @var CoordinateSystem
     */
    protected $coordinateSystem;

    /**
     * Whether this geometry is empty.
     *
     * @var bool
     */
    protected $isEmpty;

    /**
     * Class constructor.
     *
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
     *
     * @return int
     */
    abstract public function dimension() : int;

    /**
     * Returns the coordinate dimension of this geometry.
     *
     * The coordinate dimension is the total number of coordinates in the coordinate system.
     *
     * The coordinate dimension can be 2 (for x and y), 3 (with z or m added), or 4 (with both z and m added).
     * The ordinates x, y and z are spatial, and the ordinate m is a measure.
     *
     * @return int
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
     *
     * @return int
     */
    public function spatialDimension() : int
    {
        return $this->coordinateSystem->spatialDimension();
    }

    /**
     * Returns the name of the instantiable subtype of Geometry of which this Geometry is an instantiable member.
     *
     * @return string
     */
    abstract public function geometryType() : string;

    /**
     * @return int
     */
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
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function envelope() : Geometry
    {
        return GeometryEngineRegistry::get()->envelope($this);
    }

    /**
     * Returns the WKT representation of this geometry.
     *
     * @noproxy
     *
     * @return string
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
     *
     * @return string
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
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        return $this->isEmpty;
    }

    /**
     * Returns whether this geometry is valid, as defined by the OGC specification.
     *
     * For example, a polygon with self-intersecting rings is invalid.
     *
     * @noproxy
     *
     * @return bool
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function isValid() : bool
    {
        return GeometryEngineRegistry::get()->isValid($this);
    }

    /**
     * Returns whether this Geometry is simple.
     *
     * A geometry is simple if it has no anomalous geometric points,
     * such as self intersection or self tangency.
     *
     * @noproxy
     *
     * @return bool
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function isSimple() : bool
    {
        return GeometryEngineRegistry::get()->isSimple($this);
    }

    /**
     * Returns whether this geometry has z coordinate values.
     *
     * @return bool
     */
    public function is3D() : bool
    {
        return $this->coordinateSystem->hasZ();
    }

    /**
     * Returns whether this geometry has m coordinate values.
     *
     * @return bool
     */
    public function isMeasured() : bool
    {
        return $this->coordinateSystem->hasM();
    }

    /**
     * Returns the closure of the combinatorial boundary of this geometry.
     *
     * Because the result of this function is a closure, and hence topologically closed,
     * the resulting boundary can be represented using representational Geometry primitives.
     *
     * @noproxy
     *
     * @return Geometry
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function boundary() : Geometry
    {
        return GeometryEngineRegistry::get()->boundary($this);
    }

    /**
     * Returns the geometric center of a geometry, or equivalently, the center of mass of the geometry as a Point.
     * For (Multi)Points, this is computed as the arithmetic mean of the input coordinates.
     * For (Multi)Linestrings, this is computed as the weighted length of each line segment.
     * For (Multi)Polygons, "weight" is thought in terms of area.
     * If an empty geometry is supplied, an empty GeometryCollection is returned.
     * If CircularString or CompoundCurve are supplied, they are converted to LineString wtih CurveToLine first,
     * then same than for LineString.
     *
     * @noproxy
     *
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     *
     * @return Point
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function centroid() : Point
    {
        return GeometryEngineRegistry::get()->centroid($this);
    }

    /**
     * Returns whether this geometry is spatially equal to another geometry.
     *
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return bool
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function equals(Geometry $geometry) : bool
    {
        return GeometryEngineRegistry::get()->equals($this, $geometry);
    }

    /**
     * Returns whether this geometry is spatially disjoint from another geometry.
     *
     * The geometries are disjoint if they do not share any space together.
     * This is the opposite of `intersects()`.
     *
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return bool
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function disjoint(Geometry $geometry) : bool
    {
        return GeometryEngineRegistry::get()->disjoint($this, $geometry);
    }

    /**
     * Returns whether this geometry spatially intersects another geometry.
     *
     * The geometries intersect if they share any portion of space.
     * This is the opposite of `disjoint()`.
     *
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return bool
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function intersects(Geometry $geometry) : bool
    {
        return GeometryEngineRegistry::get()->intersects($this, $geometry);
    }

    /**
     * Returns whether this geometry spatially touches another geometry.
     *
     * The geometries touch if they have at least one point in common, but their interiors do not intersect.
     *
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return bool
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function touches(Geometry $geometry) : bool
    {
        return GeometryEngineRegistry::get()->touches($this, $geometry);
    }

    /**
     * Returns whether this geometry spatially crosses another geometry.
     *
     * The geometries cross if they have some, but not all, interior points in common.
     *
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return bool
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function crosses(Geometry $geometry) : bool
    {
        return GeometryEngineRegistry::get()->crosses($this, $geometry);
    }

    /**
     * Returns whether this geometry is spatially within another geometry.
     *
     * This is the inverse of `contains()`: `$a->within($b) == $b->contains($a)`.
     *
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return bool
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function within(Geometry $geometry) : bool
    {
        return GeometryEngineRegistry::get()->within($this, $geometry);
    }

    /**
     * Returns whether this geometry spatially contains another geometry.
     *
     * This is the inverse of `within()`: `$a->contains($b) == $b->within($a)`.
     *
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return bool
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function contains(Geometry $geometry) : bool
    {
        return GeometryEngineRegistry::get()->contains($this, $geometry);
    }

    /**
     * Returns whether this geometry spatially overlaps another geometry.
     *
     * The geometries overlap if they share space, but are not completely contained by each other.
     *
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return bool
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function overlaps(Geometry $geometry) : bool
    {
        return GeometryEngineRegistry::get()->overlaps($this, $geometry);
    }

    /**
     * Returns whether this geometry is spatially related to another geometry.
     *
     * This method tests for intersections between the interior, boundary and exterior of the
     * two geometries as specified by the values in the DE-9IM matrix pattern.
     *
     * This is especially useful for testing compound checks of intersection, crosses, etc. in one step.
     *
     * @see http://en.wikipedia.org/wiki/DE-9IM
     *
     * @noproxy
     *
     * @param Geometry $geometry
     * @param string   $matrix
     *
     * @return bool
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function relate(Geometry $geometry, string $matrix) : bool
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
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function locateAlong(float $mValue) : Geometry
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
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function locateBetween(float $mStart, float $mEnd) : Geometry
    {
        return GeometryEngineRegistry::get()->locateBetween($this, $mStart, $mEnd);
    }

    /**
     * Returns the shortest distance between any two points in the two geometries.
     *
     * The distance is calculated in the spatial reference system of
     * this geometry. Because the geometries are closed, it is
     * possible to find a point on each geometry involved, such
     * that the distance between these 2 points is the returned distance
     * between their geometrys.
     *
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return float
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function distance(Geometry $geometry) : float
    {
        return GeometryEngineRegistry::get()->distance($this, $geometry);
    }

    /**
     * Returns a geometry that represents all points whose distance
     * from this geometry is less than or equal to distance.
     *
     * Calculations are in the spatial reference system of this geometry.
     * Because of the limitations of linear interpolation, there will often be
     * some relatively small error in this distance, but it should be near the
     * resolution of the coordinates used.
     *
     * @noproxy
     *
     * @param float $distance
     *
     * @return Geometry
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function buffer(float $distance) : Geometry
    {
        return GeometryEngineRegistry::get()->buffer($this, $distance);
    }

    /**
     * Returns a geometry that represents the convex hull of this geometry.
     *
     * The convex hull of a geometry represents the minimum convex geometry that encloses all geometries within the set.
     * One can think of the convex hull as the geometry you get by wrapping an elastic band around a set of geometries.
     * This is different from a concave hull which is analogous to shrink-wrapping your geometries.
     *
     * @noproxy
     *
     * @return Geometry
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function convexHull() : Geometry
    {
        return GeometryEngineRegistry::get()->convexHull($this);
    }

    /**
     * Returns a geometry that represents the intersection of this geometry and another geometry.
     *
     * The intersection is the shared portion of the two geometries.
     *
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return Geometry
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function intersection(Geometry $geometry) : Geometry
    {
        return GeometryEngineRegistry::get()->intersection($this, $geometry);
    }

    /**
     * Returns a geometry that represents the union of this geometry and another geometry.
     *
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return Geometry
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function union(Geometry $geometry) : Geometry
    {
        return GeometryEngineRegistry::get()->union($this, $geometry);
    }

    /**
     * Returns a geometry that represents the difference of this geometry and another geometry.
     *
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return Geometry
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function difference(Geometry $geometry) : Geometry
    {
        return GeometryEngineRegistry::get()->difference($this, $geometry);
    }

    /**
     * Returns a geometry that represents the symmetric difference of this geometry and another geometry.
     *
     * The result is a geometry that represents the portions of the two geometries that do not intersect.
     * It is called a symmetric difference because `$a->symDifference($b) == $b->symDifference($a)`.
     *
     * @noproxy
     *
     * @param Geometry $geometry
     *
     * @return Geometry
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function symDifference(Geometry $geometry) : Geometry
    {
        return GeometryEngineRegistry::get()->symDifference($this, $geometry);
    }

    /**
     * Snap all points of this geometry to a regular grid.
     *
     * @noproxy
     *
     * @param float $size
     *
     * @return Geometry
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function snapToGrid(float $size) : Geometry
    {
        return GeometryEngineRegistry::get()->snapToGrid($this, $size);
    }

    /**
     * Returns a simplified version of this geometry using the Douglas-Peucker algorithm.
     *
     * @noproxy
     *
     * @param float $tolerance
     *
     * @return Geometry
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function simplify(float $tolerance) : Geometry
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
     *
     * @throws GeometryEngineException If the operation is not supported by the geometry engine.
     */
    public function maxDistance(Geometry $geometry) : float
    {
        return GeometryEngineRegistry::get()->maxDistance($this, $geometry);
    }

    /**
     * Returns the coordinate system of this geometry.
     *
     * @return CoordinateSystem
     */
    public function coordinateSystem() : CoordinateSystem
    {
        return $this->coordinateSystem;
    }

    /**
     * Returns a copy of this Geometry, with the SRID altered.
     *
     * @param int $srid
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
     * Returns the raw coordinates of this geometry as an array.
     *
     * @return array
     */
    abstract public function toArray() : array;

    /**
     * Returns a copy of this Geometry, with the X and Y coordinates swapped.
     *
     * @return Geometry
     */
    abstract public function swapXY() : Geometry;

    /**
     * Returns a text representation of this geometry.
     *
     * @noproxy
     *
     * @return string
     */
    final public function __toString() : string
    {
        return $this->asText();
    }
}
