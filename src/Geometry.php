<?php

namespace Brick\Geo;

use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Exception\GeometryException;
use Brick\Geo\Exception\GeometryParseException;
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
     * Whether this geometry is empty.
     *
     * @var boolean
     */
    protected $isEmpty;

    /**
     * Whether this geometry has z coordinate values.
     *
     * @var boolean
     */
    protected $is3D;

    /**
     * Whether this geometry has m coordinate values.
     *
     * @var boolean
     */
    protected $isMeasured;

    /**
     * The Spatial Reference System ID for this geometry.
     *
     * @var integer
     */
    protected $srid;

    /**
     * Private constructor. Use a factory method to obtain an instance.
     *
     * All parameters are assumed to be validated as their respective types.
     *
     * @param boolean $isEmpty    Whether this geometry is empty.
     * @param boolean $is3D       Whether this geometry has z coordinate values.
     * @param boolean $isMeasured Whether this geometry has m coordinate values.
     * @param integer $srid       The Spatial Reference System ID for this geometry.
     */
    protected function __construct($isEmpty, $is3D, $isMeasured, $srid)
    {
        $this->isEmpty    = $isEmpty;
        $this->is3D       = $is3D;
        $this->isMeasured = $isMeasured;
        $this->srid       = $srid;
    }

    /**
     * Builds a Geometry from a WKT representation.
     *
     * If the resulting geometry is valid but is not an instance of the class this method is called on,
     * for example passing a Polygon WKT to Point::fromText(), an exception is thrown.
     *
     * @param string  $wkt  The Well-Known Text representation.
     * @param integer $srid The optional SRID to use.
     *
     * @return static
     *
     * @throws GeometryParseException If the WKT data is invalid.
     * @throws GeometryException      If the geometry is invalid or of an unexpected type.
     */
    public static function fromText($wkt, $srid = 0)
    {
        static $wktReader;

        if ($wktReader === null) {
            $wktReader = new WKTReader();
        }

        $geometry = $wktReader->read($wkt, $srid);

        if (! $geometry instanceof static) {
            throw GeometryException::unexpectedGeometryType(static::class, $geometry);
        }

        return $geometry;
    }

    /**
     * Builds a Geometry from a WKB representation.
     *
     * If the resulting geometry is valid but is not an instance of the class this method is called on,
     * for example passing a Polygon WKB to Point::fromBinary(), an exception is thrown.
     *
     * @param string  $wkb  The Well-Known Binary representation.
     * @param integer $srid The optional SRID to use.
     *
     * @return static
     *
     * @throws GeometryParseException If the WKB data is invalid.
     * @throws GeometryException      If the geometry is invalid or of an unexpected type.
     */
    public static function fromBinary($wkb, $srid = 0)
    {
        static $wkbReader;

        if ($wkbReader === null) {
            $wkbReader = new WKBReader();
        }

        $geometry = $wkbReader->read($wkb, $srid);

        if (! $geometry instanceof static) {
            throw GeometryException::unexpectedGeometryType(static::class, $geometry);
        }

        return $geometry;
    }

    /**
     * Returns the inherent dimension of this geometry.
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
     * Returns the coordinate dimension of this geometry.
     *
     * The coordinate dimension can be 2 (for x and y), 3 (with z or m added), or 4 (with both z and m added).
     * The ordinates x, y and z are spatial, and the ordinate m is a measure.
     *
     * @return integer
     *
     * @throws GeometryException
     */
    public function coordinateDimension()
    {
        $coordinateDimension = 2;

        if ($this->is3D) {
            $coordinateDimension++;
        }

        if ($this->isMeasured) {
            $coordinateDimension++;
        }

        return $coordinateDimension;
    }

    /**
     * Returns the spatial dimension of this geometry.
     *
     * The spatial dimension is the number of measurements or axes needed to describe the
     * spatial position of this geometry in a coordinate system.
     *
     * @return integer
     */
    public function spatialDimension()
    {
        return $this->is3D ? 3 : 2;
    }

    /**
     * Returns the name of the instantiable subtype of Geometry of which this Geometry is an instantiable member.
     *
     * @return string
     */
    abstract public function geometryType();

    /**
     * Returns the Spatial Reference System ID for this geometry.
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
     * Returns the WKT representation of this geometry.
     *
     * @noproxy
     *
     * @return string
     */
    public function asText()
    {
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
    public function asBinary()
    {
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
     * @return boolean
     */
    public function isEmpty()
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
     * @return boolean
     */
    public function isValid()
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
     * @return boolean
     */
    public function isSimple()
    {
        return GeometryEngineRegistry::get()->isSimple($this);
    }

    /**
     * Returns whether this geometry has z coordinate values.
     *
     * @return boolean
     */
    public function is3D()
    {
        return $this->is3D;
    }

    /**
     * Returns whether this geometry has m coordinate values.
     *
     * @return boolean
     */
    public function isMeasured()
    {
        return $this->isMeasured;
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
     */
    public function boundary()
    {
        return GeometryEngineRegistry::get()->boundary($this);
    }

    /**
     * Returns whether this geometry is spatially equal to another geometry.
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
     * Returns whether this geometry is spatially disjoint from another geometry.
     *
     * The geometries are disjoint if they do not share any space together.
     * This is the opposite of `intersects()`.
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
     * Returns whether this geometry spatially intersects another geometry.
     *
     * The geometries intersect if they share any portion of space.
     * This is the opposite of `disjoint()`.
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
     * Returns whether this geometry spatially touches another geometry.
     *
     * The geometries touch if they have at least one point in common, but their interiors do not intersect.
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
     * Returns whether this geometry spatially crosses another geometry.
     *
     * The geometries cross if they have some, but not all, interior points in common.
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
     * Returns whether this geometry is spatially within another geometry.
     *
     * This is the inverse of `contains()`: `$a->within($b) == $b->contains($a)`.
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
     * Returns whether this geometry spatially contains another geometry.
     *
     * This is the inverse of `within()`: `$a->contains($b) == $b->within($a)`.
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
     * Returns whether this geometry spatially overlaps another geometry.
     *
     * The geometries overlap if they share space, but are not completely contained by each other.
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
     */
    public function distance(Geometry $geometry)
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
     */
    public function buffer($distance)
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
     */
    public function convexHull()
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
     */
    public function intersection(Geometry $geometry)
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
     */
    public function union(Geometry $geometry)
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
     */
    public function difference(Geometry $geometry)
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
     */
    public function symDifference(Geometry $geometry)
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
     */
    public function snapToGrid($size)
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
     * Returns the raw coordinates of this geometry as an array.
     *
     * @return array
     */
    abstract public function toArray();

    /**
     * Returns a text representation of this geometry.
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
                if ($geometry->is3D() !== $is3D || $geometry->isMeasured() !== $isMeasured) {
                    throw GeometryException::dimensionalityMix($previous, $geometry);
                }
                if ($geometry->SRID() !== $srid) {
                    throw new GeometryException('Incompatible SRID: %d and %d.', $srid, $geometry->SRID());
                }
            }
        }
    }

    /**
     * @param array   $geometries   The geometries to check.
     * @param string  $className    The expected FQCN of the geometries.
     * @param boolean $is3D         Whether the geometries are expected to have a Z coordinate.
     * @param boolean $isMeasured   Whether the geometries are expected to have a M coordinate.
     * @param integer $srid         The expected SRID of the geometries.
     *
     * @return void
     *
     * @throws GeometryException
     */
    protected static function checkGeometries(array $geometries, $className, $is3D, $isMeasured, $srid)
    {
        $reflectionClass = new \ReflectionClass(static::class);
        $geometryType = $reflectionClass->getShortName();

        foreach ($geometries as $geometry) {
            if (! $geometry instanceof $className) {
                throw new GeometryException(sprintf(
                    '%s can only contain %s objects, %s given.',
                    static::class,
                    $className,
                    is_object($geometry) ? get_class($geometry) : gettype($geometry)
                ));
            }

            /** @var Geometry $geometry */

            if ($geometry->is3D() !== $is3D || $geometry->isMeasured() !== $isMeasured) {
                throw GeometryException::incompatibleDimensionality($geometry, $geometryType, $is3D, $isMeasured);
            }

            if ($geometry->SRID() !== $srid) {
                throw GeometryException::incompatibleSRID($geometry, $geometryType, $srid);
            }
        }
    }
}
