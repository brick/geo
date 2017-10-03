<?php

/**
 * GEOSGeometry class stub.
 *
 * These stubs are required for IDEs to provide autocompletion and static code analysis during development.
 * They are not required for production.
 *
 * @see https://github.com/libgeos/libgeos/blob/svn-trunk/php/geos.c
 */
class GEOSGeometry
{
    /**
     * This method is actually public, but throws a fatal error.
     *
     * GEOSGeometry can't be constructed using new, check WKTReader.
     */
    private function __construct() {}

    /**
     * @return string
     */
    public function __toString() {}

    /**
     * @param GEOSGeometry $other
     * @param bool         $normalized
     *
     * @return float
     *
     * @throws \Exception
     */
    public function project(GEOSGeometry $other, $normalized = false) {}

    /**
     * @param float $dist
     * @param bool  $normalized
     *
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function interpolate($dist, $normalized = false) {}

    /**
     * @param float $dist
     * @param array $styleArray
     *
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function buffer($dist, array $styleArray = []) {}

    /**
     * @param float $dist
     * @param array $styleArray
     *
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function offsetCurve($dist, array $styleArray = []) {}

    /**
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function envelope() {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function intersection(GEOSGeometry $geom) {}

    /**
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function convexHull() {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function difference(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function symDifference(GEOSGeometry $geom) {}

    /**
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function boundary() {}

    /**
     * @param GEOSGeometry $otherGeom Optional, but explicit NULL is not allowed.
     *
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function union(GEOSGeometry $otherGeom = null) {}

    /**
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function pointOnSurface() {}

    /**
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function centroid() {}

    /**
     * @param GEOSGeometry $otherGeom
     * @param string       $pattern
     *
     * @return string|bool String if pattern is omitted, boolean if pattern is set.
     *
     * @throws \Exception
     */
    public function relate(GEOSGeometry $otherGeom, $pattern = '') {}

    /**
     * @param GEOSGeometry $otherGeom
     * @param int          $rule
     *
     * @return string
     *
     * @throws \Exception
     */
    public function relateBoundaryNodeRule(GEOSGeometry $otherGeom, $rule) {}

    /**
     * @param float $tolerance
     * @param bool  $preserveTopology
     *
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function simplify($tolerance, $preserveTopology = false) {}

    /**
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function normalize() {}

    /**
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function extractUniquePoints() {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function disjoint(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function touches(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function intersects(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function crosses(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function within(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function contains(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function overlaps(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function covers(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function coveredBy(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function equals(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     * @param float        $tolerance
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function equalsExact(GEOSGeometry $geom, $tolerance = 0.0) {}

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function isEmpty() {}

    /**
     * Returns information about the validity of this geometry.
     *
     * The result is an associative array containing the following keys:
     *
     * | Key name   | Type         | Presence | Description                                     |
     * |------------|--------------|----------|-------------------------------------------------|
     * | 'valid'    | boolean      | Always   | True if the geometry is valid, False otherwise. |
     * | 'reason'   | string       | Optional | A reason for invalidity.                        |
     * | 'location' | GEOSGeometry | Optional | The failing geometry.                           |
     *
     * @return array
     *
     * @throws \Exception
     */
    public function checkValidity() {}

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function isSimple() {}

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function isRing() {}

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function hasZ() {}

    /**
     * @return bool
     *
     * @throws \Exception On error, e.g. if this geometry is not a LineString.
     */
    public function isClosed() {}

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function typeName() {}

    /**
     * @return int
     *
     * @throws \Exception
     */
    public function typeId() {}

    /**
     * @return int
     *
     * @throws \Exception
     */
    public function getSRID() {}

    /**
     * @param int $srid
     *
     * @return void
     *
     * @throws \Exception
     */
    public function setSRID($srid) {}

    /**
     * @return int
     *
     * @throws \Exception
     */
    public function numGeometries() {}

    /**
     * @param int $num
     *
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function geometryN($num) {}

    /**
     * @return int
     *
     * @throws \Exception
     */
    public function numInteriorRings() {}

    /**
     * @return int
     *
     * @throws \Exception
     */
    public function numPoints() {}

    /**
     * @return float
     *
     * @throws \Exception
     */
    public function getX() {}

    /**
     * @return float
     *
     * @throws \Exception
     */
    public function getY() {}

    /**
     * @param int $num
     *
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function interiorRingN($num) {}

    /**
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function exteriorRing() {}

    /**
     * @return int
     *
     * @throws \Exception
     */
    public function numCoordinates() {}

    /**
     * @return int
     *
     * @throws \Exception
     */
    public function dimension() {}

    /**
     * @return int
     *
     * @throws \Exception
     */
    public function coordinateDimension() {}

    /**
     * @param int $num
     *
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function pointN($num) {}

    /**
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function startPoint() {}

    /**
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function endPoint() {}

    /**
     * @return float
     *
     * @throws \Exception
     */
    public function area() {}

    /**
     * @return float
     *
     * @throws \Exception
     */
    public function length() {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return float
     *
     * @throws \Exception
     */
    public function distance(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return float
     *
     * @throws \Exception
     */
    public function hausdorffDistance(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     * @param float        $tolerance
     *
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function snapTo(GEOSGeometry $geom, $tolerance) {}

    /**
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function node() {}

    /**
     * @param float $tolerance Snapping tolerance to use for improved robustness.
     * @param bool  $onlyEdges If true will return a MULTILINESTRING, otherwise (the default)
     *                           it will return a GEOMETRYCOLLECTION containing triangular POLYGONs.
     *
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function delaunayTriangulation($tolerance = 0.0, $onlyEdges = false) {}

    /**
     * @param float        $tolerance Snapping tolerance to use for improved robustness.
     * @param bool         $onlyEdges If true will return a MULTILINESTRING, otherwise (the default)
     *                                it will return a GEOMETRYCOLLECTION containing POLYGONs.
     * @param GEOSGeometry $extent    Clip returned diagram by the extent of the given geometry.
     *                                Optional, but explicit NULL value is not allowed.
     *
     * @return GEOSGeometry
     *
     * @throws \Exception
     */
    public function voronoiDiagram($tolerance = 0.0, $onlyEdges = false, GEOSGeometry $extent = null) {}
}
