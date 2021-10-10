<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Curve;
use Brick\Geo\Geometry;
use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\MultiCurve;
use Brick\Geo\MultiSurface;
use Brick\Geo\Point;
use Brick\Geo\Surface;

/**
 * Interface for geometry engines.
 */
interface GeometryEngine
{
    /**
     * Returns a geometry that represents the point set union of the geometries.
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
     * @param Curve|MultiCurve $g The geometry.
     *
     * @return float The length of the geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function length(Geometry $g) : float;

    /**
     * Returns the area of a Surface or MultiSurface in its SRID units.
     *
     * @param Surface|MultiSurface $g The geometry.
     *
     * @return float The area of the geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function area(Geometry $g) : float;

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
     * Returns the geometric center of a Geometry.
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
     * @return Geometry A point of the surface of the geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function pointOnSurface(Geometry $g) : Geometry;

    /**
     * Returns the closure of the combinatorial boundary of a Geometry.
     *
     * @param Geometry $g The geometry.
     *
     * @return Geometry The boundary of the geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function boundary(Geometry $g) : Geometry;

    /**
     * Checks whether a geometry is valid, as defined by the OGC specification.
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
     * Returns true if the given geometries represent the same geometry.
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
     * Returns true if the given geometries do not spatially intersect.
     *
     * Geometries spatially intersect if they share any portion of space.
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
     * Returns true if the geometries have at least one point in common, but their interiors do not intersect.
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
     * Returns true if the supplied geometries have some, but not all, interior points in common.
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
     * @param Geometry $a The first geometry.
     * @param Geometry $b The second geometry.
     *
     * @return bool Whether the first geometry is within the second.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function within(Geometry $a, Geometry $b) : bool;

    /**
     * Returns true if `$a` contains `$b`.
     *
     * `$a` contains `$b` if and only if no points of `$b` lie in the exterior of `$a`,
     * and at least one point of the interior of `$b` lies in the interior of `$a`.
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
     * Returns true if the two geometries overlap.
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
     * Tests for intersections between the Interior, Boundary and Exterior
     * of the two geometries as specified by the values in the intersectionMatrixPattern.
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
     * Returns the 2-dimensional cartesian minimum distance between two geometries in projected units.
     *
     * The distance is based on spatial ref.
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
     * @param Geometry $g        The geometry.
     * @param float    $distance The buffer distance.
     *
     * @return Geometry The buffer geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function buffer(Geometry $g, float $distance) : Geometry;

    /**
     * Returns the minimum convex geometry that encloses all geometries within the set.
     *
     * @param Geometry $g The geometry.
     *
     * @return Geometry The convex hull geometry.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function convexHull(Geometry $g) : Geometry;

    /**
     * Returns a geometry that represents the shared portion of `$a` and `$b`.
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
     * Returns a geometry that represents the portions of `$a` and `$b` that do not intersect.
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
     * Returns a "simplified" version of the given geometry using the Douglas-Peucker algorithm.
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
     * Returns the collection of polygons that bounds the given polygon 'p' for any polygon 'p' in the surface.
     *
     * @param Geometry $g
     *
     * @return Geometry
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     */
    public function boundingPolygons(Geometry $g) : Geometry;

    /**
     * Returns a new geometry with its coordinates transformed to a different spatial reference system.
     */
    public function transform(Geometry $g, int $srid) : Geometry;
}
