<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Curve;
use Brick\Geo\Geometry;
use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\LineString;
use Brick\Geo\MultiCurve;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiPolygon;
use Brick\Geo\MultiSurface;
use Brick\Geo\Point;
use Brick\Geo\Polygon;
use Brick\Geo\Surface;

/**
 * Interface for geometry engines.
 */
interface GeometryEngine
{
    /**
     * Returns a geometry that represents the union of the geometries.
     *
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return Geometry The union of the geometries.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function union(Geometry $a, Geometry $b) : Geometry;

    /**
     * Returns a geometry that represents that part of `$a` that does not intersect with `$b`.
     *
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return Geometry The difference of the geometries.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function difference(Geometry $a, Geometry $b) : Geometry;

    /**
     * Returns a geometry representing the bounding box of the supplied geometry.
     *
     * The polygon is defined by the corner points of the bounding box
     * [(MINX, MINY), (MAXX, MINY), (MAXX, MAXY), (MINX, MAXY), (MINX, MINY)].
     * Minimums for Z and M may be added. The simplest representation of an Envelope
     * is as two direct positions, one containing all the minimums, and another all
     * the maximums. In some cases, this coordinate will be outside the range of
     * validity for the Spatial Reference System.
     *
     * @param Geometry $g The geometry.
     *
     * @return Geometry The envelope of the geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function envelope(Geometry $g) : Geometry;

    /**
     * Returns the length of a Curve or MultiCurve in its associated spatial reference.
     *
     * The length of a MultiCurve is equal to the sum of the lengths of the element Curves.
     *
     * @param Curve|MultiCurve $g The geometry.
     *
     * @return float The length of the geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function length(Curve|MultiCurve $g) : float;

    /**
     * Returns the area of a Surface or MultiSurface in its SRID units.
     *
     * @param Surface|MultiSurface $g The geometry.
     *
     * @return float The area of the geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function area(Surface|MultiSurface $g) : float;

    /**
     * Returns the azimuth in radians of the segment defined by the given point geometries.
     * The azimuth is an angle measured from the north, and is positive clockwise:
     * North = 0; East = π/2; South = π; West = 3π/2.
     *
     * @param Point $observer Point representing observer.
     * @param Point $subject  Point representing subject of observation.
     *
     * @return float Azimuth of the subject relative to the observer.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     * @throws GeometryEngineException If observer and subject locations are coincident.
     */
    public function azimuth(Point $observer, Point $subject) : float;

    /**
     * Returns the geometric center of a geometry, or equivalently, the center of mass of the geometry as a Point.
     *
     * @param Geometry $g The geometry.
     *
     * @return Point The centroid of the geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function centroid(Geometry $g) : Point;

    /**
     * Returns a Point guaranteed to be on a Surface or MultiSurface.
     *
     * @param Surface|MultiSurface $g The geometry.
     *
     * @return Point A point of the surface of the geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function pointOnSurface(Surface|MultiSurface $g) : Point;

    /**
     * Returns the closure of the combinatorial boundary of a Geometry.
     *
     * Because the result of this function is a closure, and hence topologically closed,
     * the resulting boundary can be represented using representational Geometry primitives.
     *
     * @param Geometry $g The geometry.
     *
     * @return Geometry The boundary of the geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function boundary(Geometry $g) : Geometry;

    /**
     * Returns true if the geometry is valid, as defined by the OGC specification.
     *
     * For example, a polygon with self-intersecting rings is invalid.
     *
     * @param Geometry $g The geometry.
     *
     * @return bool Whether the geometry is valid.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function isValid(Geometry $g) : bool;

    /**
     * Returns true if the geometry is closed.
     *
     * A Curve is closed if its start point is equal to its end point.
     * A MultiCurve is considered closed if each element curve is closed.
     *
     * @param Geometry $g The geometry.
     *
     * @return bool Whether the geometry is closed.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function isClosed(Geometry $g) : bool;

    /**
     * Returns true if the geometry has no anomalous geometric points, such as self intersection or self tangency.
     *
     * @param Geometry $g The geometry.
     *
     * @return bool Whether the geometry is simple.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function isSimple(Geometry $g) : bool;

    /**
     * Returns true if the curve is a ring, i.e. if it is both closed and simple.
     *
     * The curve is closed if its start point is equal to its end point.
     * The curve is simple if it does not pass through the same point more than once.
     *
     * @param Curve $curve The curve.
     *
     * @return bool Whether the curve is a ring.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function isRing(Curve $curve) : bool;

    /**
     * Attempts to create a valid representation of a given invalid geometry without losing any of the input vertices.
     *
     * Valid geometries are returned unchanged.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function makeValid(Geometry $g) : Geometry;

    /**
     * Returns true if the given geometries are spatially equal.
     *
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return bool Whether the geometries are spatially equal.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function equals(Geometry $a, Geometry $b) : bool;

    /**
     * Returns true if the given geometries are spatially disjoint.
     *
     * The geometries are disjoint if they do not share any space together.
     * This is the opposite of `intersects()`.
     *
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return bool Whether the geometries are disjoint.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function disjoint(Geometry $a, Geometry $b) : bool;

    /**
     * Returns true if the given geometries spatially intersect.
     *
     * Geometries spatially intersect if they share any portion of space.
     * This is the opposite of `disjoint()`.
     *
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return bool Whether the geometries intersect.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function intersects(Geometry $a, Geometry $b) : bool;

    /**
     * Returns true if the geometries spatially touch each other.
     *
     * The geometries touch if they have at least one point in common, but their interiors do not intersect.
     *
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return bool Whether the geometries touch.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function touches(Geometry $a, Geometry $b) : bool;

    /**
     * Returns true if the supplied geometries spatially cross each other.
     *
     * The geometries cross if they have some, but not all, interior points in common.
     *
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return bool Whether the geometries cross.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function crosses(Geometry $a, Geometry $b) : bool;

    /**
     * Returns true if the geometry $a is completely inside geometry $b.
     *
     * This is the inverse of `contains()`: `within($a, $b) == contains($b, $a)`.
     *
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return bool Whether the first geometry is within the second.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function within(Geometry $a, Geometry $b) : bool;

    /**
     * Returns true if `$a` spatially contains `$b`.
     *
     * `$a` contains `$b` if and only if no points of `$b` lie in the exterior of `$a`,
     * and at least one point of the interior of `$b` lies in the interior of `$a`.
     *
     * This is the inverse of `within()`: `$a->contains($b) == $b->within($a)`.
     *
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return bool Whether the first geometry contains the second.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function contains(Geometry $a, Geometry $b) : bool;

    /**
     * Returns true if the two geometries spatially overlap.
     *
     * The geometries overlap if they share space, are of the same dimension,
     * but are not completely contained by each other.
     *
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return bool Whether the geometries overlap.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function overlaps(Geometry $a, Geometry $b) : bool;

    /**
     * Returns true if `$a` is spatially related to `$b`.
     *
     * This method tests for intersections between the interior, boundary and exterior of the
     * two geometries as specified by the values in the DE-9IM matrix pattern.
     *
     * This is especially useful for testing compound checks of intersection, crosses, etc. in one step.
     *
     * @see http://en.wikipedia.org/wiki/DE-9IM
     *
     * @param Geometry $a      The first geometry.
     * @param Geometry $b      The second geometry.
     * @param string   $matrix The DE-9IM matrix pattern.
     *
     * @return bool Whether the geometries relate according to the matrix pattern.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function relate(Geometry $a, Geometry $b, string $matrix) : bool;

    /**
     * Returns a derived geometry collection value with elements that match the specified measure.
     *
     * @param Geometry $g      The geometry.
     * @param float    $mValue The m coordinate value.
     *
     * @return Geometry The elements that match the measure.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function locateAlong(Geometry $g, float $mValue) : Geometry;

    /**
     * Returns a derived geometry collection value with elements that match the specified range of measures inclusively.
     *
     * @param Geometry $g      The geometry.
     * @param float    $mStart The start of m coordinates.
     * @param float    $mEnd   The end of m coordinates.
     *
     * @return Geometry The elements that match the measures.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function locateBetween(Geometry $g, float $mStart, float $mEnd) : Geometry;

    /**
     * Returns the shortest distance between any two points in the two geometries.
     *
     * The distance is calculated in the spatial reference system of
     * this geometry. Because the geometries are closed, it is
     * possible to find a point on each geometry involved, such
     * that the distance between these 2 points is the returned distance
     * between their geometries.
     *
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return float The distance between the geometries.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function distance(Geometry $a, Geometry $b) : float;

    /**
     * Returns a geometry that represents all points whose distance from this Geometry is <= distance.
     *
     * Calculations are in the spatial reference system of this geometry.
     * Because of the limitations of linear interpolation, there will often be
     * some relatively small error in this distance, but it should be near the
     * resolution of the coordinates used.
     *
     * @param Geometry $g        The geometry.
     * @param float    $distance The buffer distance.
     *
     * @return Geometry The buffer geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function buffer(Geometry $g, float $distance) : Geometry;

    /**
     * Returns a geometry that represents the convex hull of this geometry.
     *
     * The convex hull of a geometry represents the minimum convex geometry that encloses all geometries within the set.
     * One can think of the convex hull as the geometry you get by wrapping an elastic band around a set of geometries.
     * This is different from a concave hull which is analogous to shrink-wrapping your geometries.
     *
     * @param Geometry $g The geometry.
     *
     * @return Geometry The convex hull geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function convexHull(Geometry $g) : Geometry;

    /**
     * Returns a geometry that represents the shared portion of the given geometries.
     *
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return Geometry The intersection of the geometries.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function intersection(Geometry $a, Geometry $b) : Geometry;

    /**
     * Returns a geometry that represents the symmetric difference of the given geometries.
     *
     * The result is a geometry that represents the portions of the two geometries that do not intersect.
     * It is called a symmetric difference because `$a->symDifference($b) == $b->symDifference($a)`.
     *
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return Geometry The symmetric difference of the geometries.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function symDifference(Geometry $a, Geometry $b) : Geometry;

    /**
     * Snap all points of the input geometry to a regular grid.
     *
     * @param Geometry $g    The geometry.
     * @param float    $size The grid size.
     *
     * @return Geometry The snapped geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function snapToGrid(Geometry $g, float $size) : Geometry;

    /**
     * Returns a simplified version of the given geometry using the Douglas-Peucker algorithm.
     *
     * @param Geometry $g         The geometry.
     * @param float    $tolerance The tolerance.
     *
     * @return Geometry The simplified geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function simplify(Geometry $g, float $tolerance) : Geometry;

    /**
     * Returns the 2-dimensional largest distance between two geometries in projected units.
     *
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return float The max distance between the geometries.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function maxDistance(Geometry $a, Geometry $b) : float;

    /**
     * Returns a new geometry with its coordinates transformed to a different spatial reference system.
     */
    public function transform(Geometry $g, int $srid) : Geometry;

    /**
     * Splits a geometry into several geometries using a blade.
     */
    public function split(Geometry $g, Geometry $blade) : Geometry;

    /**
     * Returns a point interpolated along a line at a fractional location.
     *
     * @param LineString $linestring The linestring.
     * @param float $fraction Is a float between 0.0 and 1.0 representing the fraction of line length where the point is to be located.
     *
     * @return Point The point.
     */
    public function lineInterpolatePoint(LineString $linestring, float $fraction) : Point;

    /**
     * Returns one or more points interpolated along a line at a fractional interval.
     *
     * @param LineString $linestring The linestring.
     * @param float $fraction Is a float between 0.0 and 1.0 representing the spacing between the points as a fraction of line length.
     *
     * @return Point|MultiPoint The MultiPoint or Point.
     */
    public function lineInterpolatePoints(LineString $linestring, float $fraction) : Point|MultiPoint;
}
