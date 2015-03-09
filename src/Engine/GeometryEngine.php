<?php

namespace Brick\Geo\Engine;

use Brick\Geo\Geometry;

/**
 * Interface for geometry engines.
 */
interface GeometryEngine
{
    /**
     * Returns a geometry that represents the point set union of the geometries.
     *
     * @param Geometry $a
     * @param Geometry $b
     *
     * @return Geometry
     */
    public function union(Geometry $a, Geometry $b);

    /**
     * Returns a geometry that represents that part of `$a` that does not intersect with `$b`.
     *
     * @param Geometry $a
     * @param Geometry $b
     *
     * @return Geometry
     */
    public function difference(Geometry $a, Geometry $b);

    /**
     * Returns a geometry representing the bounding box of the supplied geometry.
     *
     * @param Geometry $g
     *
     * @return Geometry
     */
    public function envelope(Geometry $g);

    /**
     * Returns the length of a Curve or MultiCurve in its associated spatial reference.
     *
     * @param Geometry $g
     *
     * @return float
     */
    public function length(Geometry $g);

    /**
     * Returns the area of a Surface or MultiSurface in its SRID units.
     *
     * @param Geometry $g
     *
     * @return float
     */
    public function area(Geometry $g);

    /**
     * Returns the geometric center of a Surface or MultiSurface.
     *
     * @param Geometry $g
     *
     * @return Geometry
     */
    public function centroid(Geometry $g);

    /**
     * Returns the closure of the combinatorial boundary of a Geometry.
     *
     * @param Geometry $g
     *
     * @return Geometry
     */
    public function boundary(Geometry $g);

    /**
     * Returns true if the geometry has no anomalous geometric points, such as self intersection or self tangency.
     *
     * @param Geometry $g
     *
     * @return boolean
     */
    public function isSimple(Geometry $g);

    /**
     * Returns true if the given geometries represent the same geometry.
     *
     * @param Geometry $a
     * @param Geometry $b
     *
     * @return boolean
     */
    public function equals(Geometry $a, Geometry $b);

    /**
     * Returns true if the given geometries do not spatially intersect.
     *
     * Geometries spatially intersect if they share any portion of space.
     *
     * @param Geometry $a
     * @param Geometry $b
     *
     * @return boolean
     */
    public function disjoint(Geometry $a, Geometry $b);

    /**
     * Returns true if the given geometries spatially intersect.
     *
     * Geometries spatially intersect if they share any portion of space.
     *
     * @param Geometry $a
     * @param Geometry $b
     *
     * @return boolean
     */
    public function intersects(Geometry $a, Geometry $b);

    /**
     * Returns true if the geometries have at least one point in common, but their interiors do not intersect.
     *
     * @param Geometry $a
     * @param Geometry $b
     *
     * @return boolean
     */
    public function touches(Geometry $a, Geometry $b);

    /**
     * Returns true if the supplied geometries have some, but not all, interior points in common.
     *
     * @param Geometry $a
     * @param Geometry $b
     *
     * @return boolean
     */
    public function crosses(Geometry $a, Geometry $b);

    /**
     * Returns true if the geometry $a is completely inside geometry $b.
     *
     * @param Geometry $a
     * @param Geometry $b
     *
     * @return boolean
     */
    public function within(Geometry $a, Geometry $b);

    /**
     * Returns true if `$a` contains `$b`.
     *
     * `$a` contains `$b` if and only if no points of `$b` lie in the exterior of `$a`,
     * and at least one point of the interior of `$b` lies in the interior of `$a`.
     *
     * @param Geometry $a
     * @param Geometry $b
     *
     * @return boolean
     */
    public function contains(Geometry $a, Geometry $b);

    /**
     * Returns true if the two geometries overlap.
     *
     * The geometries overlap if they share space, are of the same dimension,
     * but are not completely contained by each other.
     *
     * @param Geometry $a
     * @param Geometry $b
     *
     * @return boolean
     */
    public function overlaps(Geometry $a, Geometry $b);

    /**
     * Returns true if `$a` is spatially related to `$b`.
     *
     * Tests for intersections between the Interior, Boundary and Exterior
     * of the two geometries as specified by the values in the intersectionMatrixPattern.
     *
     * @param Geometry $a
     * @param Geometry $b
     * @param string   $matrix
     *
     * @return boolean
     */
    public function relate(Geometry $a, Geometry $b, $matrix);

    /**
     * Returns a derived geometry collection value with elements that match the specified measure.
     *
     * @param Geometry $g
     * @param float    $mValue
     *
     * @return Geometry
     */
    public function locateAlong(Geometry $g, $mValue);

    /**
     * Returns a derived geometry collection value with elements that match the specified range of measures inclusively.
     *
     * @param Geometry $g
     * @param float    $mStart
     * @param float    $mEnd
     *
     * @return Geometry
     */
    public function locateBetween(Geometry $g, $mStart, $mEnd);

    /**
     * Returns the 2-dimensional cartesian minimum distance between two geometries in projected units.
     *
     * The distance is based on spatial ref.
     *
     * @param Geometry $a
     * @param Geometry $b
     *
     * @return float
     */
    public function distance(Geometry $a, Geometry $b);

    /**
     * Returns a geometry that represents all points whose distance from this Geometry is <= distance.
     *
     * @param Geometry $g
     * @param float    $distance
     *
     * @return Geometry
     */
    public function buffer(Geometry $g, $distance);

    /**
     * Returns the minimum convex geometry that encloses all geometries within the set.
     *
     * @param Geometry $g
     *
     * @return Geometry
     */
    public function convexHull(Geometry $g);

    /**
     * Returns a geometry that represents the shared portion of `$a` and `$b`.
     *
     * @param Geometry $a
     * @param Geometry $b
     *
     * @return Geometry
     */
    public function intersection(Geometry $a, Geometry $b);

    /**
     * Returns a geometry that represents the portions of `$a` and `$b` that do not intersect.
     *
     * @param Geometry $a
     * @param Geometry $b
     *
     * @return Geometry
     */
    public function symDifference(Geometry $a, Geometry $b);

    /**
     * Snap all points of the input geometry to a regular grid.
     *
     * @param Geometry $a
     * @param float $b size
     * @return Geometry
     */
    public function snapToGrid(Geometry $a, $b);

    /**
     * Returns a "simplified" version of the given geometry using the Douglas-Peucker algorithm.
     *
     * @param Geometry $a
     * @param float $b tolerance
     * @return mixed
     */
    public function simplify(Geometry $a, $b);
}
