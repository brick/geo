<?php

namespace Brick\Geo\Service;

use Brick\Geo\Geometry;

/**
 * Services implementing this interface can be statically injected
 * into the Geometry class, to provide it with missing PHP implementations.
 */
interface GeometryService
{
    /**
     * Returns a geometry that represents the
     * point set union of the geometries
     *
     * @param  \Brick\Geo\Geometry $a
     * @param  \Brick\Geo\Geometry $b
     * @return \Brick\Geo\Geometry
     */
    public function union(Geometry $a, Geometry $b);

    /**
     * Returns a geometry that represents that part of
     * geometry $a that does not intersect with geometry $b
     *
     * @param  \Brick\Geo\Geometry $a
     * @param  \Brick\Geo\Geometry $b
     * @return \Brick\Geo\Geometry
     */
    public function difference(Geometry $a, Geometry $b);

    /**
     * Returns a geometry representing the bounding box
     * of the supplied geometry
     *
     * @param  \Brick\Geo\Geometry $g
     * @return \Brick\Geo\Geometry
     */
    public function envelope(Geometry $g);

    /**
     * Returns the length of a Curve or MultiCurve
     * in its associated spatial reference.
     *
     * @param  \Brick\Geo\Geometry $g
     * @return float
     */
    public function length(Geometry $g);

    /**
     * Returns the area of a Surface or MultiSurface
     * in its SRID units.
     *
     * @param  \Brick\Geo\Geometry $g
     * @return float
     */
    public function area(Geometry $g);

    /**
     * Returns the geometric center of a Surface or MultiSurface.
     *
     * @param  \Brick\Geo\Geometry $g
     * @return \Brick\Geo\Geometry
     */
    public function centroid(Geometry $g);

    /**
     * Returns the closure of the combinatorial boundary of a Geometry.
     *
     * @param  \Brick\Geo\Geometry $g
     * @return \Brick\Geo\Geometry
     */
    public function boundary(Geometry $g);

    /**
     * Returns true if the geometry has no anomalous geometric points,
     * such as self intersection or self tangency.
     *
     * @param  \Brick\Geo\Geometry $g
     * @return boolean
     */
    public function isSimple(Geometry $g);

    /**
     * Returns true if the given geometries represent the same geometry.
     *
     * @param  \Brick\Geo\Geometry $a
     * @param  \Brick\Geo\Geometry $b
     * @return boolean
     */
    public function equals(Geometry $a, Geometry $b);

    /**
     * Returns true if the given geometries do not "spatially intersect"
     * (if they do not share any space together).
     *
     * @param  \Brick\Geo\Geometry $a
     * @param  \Brick\Geo\Geometry $b
     * @return boolean
     */
    public function disjoint(Geometry $a, Geometry $b);

    /**
     * Returns true if the given geometries "spatially intersect"
     * (share any portion of space) and false if they don't (they are disjoint).
     *
     * @param  \Brick\Geo\Geometry $a
     * @param  \Brick\Geo\Geometry $b
     * @return boolean
     */
    public function intersects(Geometry $a, Geometry $b);

    /**
     * Returns true if the geometries have at least one point in common,
     * but their interiors do not intersect.
     *
     * @param  \Brick\Geo\Geometry $a
     * @param  \Brick\Geo\Geometry $b
     * @return boolean
     */
    public function touches(Geometry $a, Geometry $b);

    /**
     * Returns true if the supplied geometries have some,
     * but not all, interior points in common.
     *
     * @param  \Brick\Geo\Geometry $a
     * @param  \Brick\Geo\Geometry $b
     * @return boolean
     */
    public function crosses(Geometry $a, Geometry $b);

    /**
     * Returns true if the geometry $a is completely inside geometry $b.
     *
     * @param  \Brick\Geo\Geometry $a
     * @param  \Brick\Geo\Geometry $b
     * @return boolean
     */
    public function within(Geometry $a, Geometry $b);

    /**
     * Returns true if and only if no points of $b lie in the exterior of $a,
     * and at least one point of the interior of $b lies in the interior of $a.
     *
     * @param  \Brick\Geo\Geometry $a
     * @param  \Brick\Geo\Geometry $b
     * @return boolean
     */
    public function contains(Geometry $a, Geometry $b);

    /**
     * Returns true if the geometries share space, are of the same dimension,
     * but are not completely contained by each other.
     *
     * @param  \Brick\Geo\Geometry $a
     * @param  \Brick\Geo\Geometry $b
     * @return boolean
     */
    public function overlaps(Geometry $a, Geometry $b);

    /**
     * Returns true if this $a is spatially related to $b,
     * by testing for intersections between the Interior, Boundary and Exterior
     * of the two geometries as specified by the values in the intersectionMatrixPattern.
     *
     * @param  \Brick\Geo\Geometry $a
     * @param  \Brick\Geo\Geometry $b
     * @param  string               $intersectionMatrixPattern
     * @return boolean
     */
    public function relate(Geometry $a, Geometry $b, $intersectionMatrixPattern);

    /**
     * Returns a derived geometry collection value
     * with elements that match the specified measure.
     *
     * @param  \Brick\Geo\Geometry $g
     * @param  float                $mValue
     * @return \Brick\Geo\Geometry
     */
    public function locateAlong(Geometry $g, $mValue);

    /**
     * Returns a derived geometry collection value with elements
     * that match the specified range of measures inclusively.
     *
     * @param  \Brick\Geo\Geometry $g
     * @param  float                $mStart
     * @param  float                $mEnd
     * @return \Brick\Geo\Geometry
     */
    public function locateBetween(Geometry $g, $mStart, $mEnd);

    /**
     * Returns the 2-dimensional cartesian minimum distance
     * (based on spatial ref) between two geometries in projected units.
     *
     * @param  \Brick\Geo\Geometry $a
     * @param  \Brick\Geo\Geometry $b
     * @return float
     */
    public function distance(Geometry $a, Geometry $b);

    /**
     * Returns a geometry that represents all points whose distance
     * from this Geometry is less than or equal to distance.
     *
     * @param  \Brick\Geo\Geometry $g
     * @param  float                $distance
     * @return \Brick\Geo\Geometry
     */
    public function buffer(Geometry $g, $distance);

    /**
     * Returns the minimum convex geometry that encloses
     * all geometries within the set.
     *
     * @param  \Brick\Geo\Geometry $g
     * @return \Brick\Geo\Geometry
     */
    public function convexHull(Geometry $g);

    /**
     * Returns a geometry that represents the shared portion of $a and $b.
     *
     * @param  \Brick\Geo\Geometry $a
     * @param  \Brick\Geo\Geometry $b
     * @return \Brick\Geo\Geometry
     */
    public function intersection(Geometry $a, Geometry $b);

    /**
     * Returns a geometry that represents the portions
     * of $a and $b that do not intersect.
     *
     * @param  \Brick\Geo\Geometry $a
     * @param  \Brick\Geo\Geometry $b
     * @return \Brick\Geo\Geometry
     */
    public function symDifference(Geometry $a, Geometry $b);
}
