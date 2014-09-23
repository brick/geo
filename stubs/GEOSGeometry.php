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
     * @param boolean      $normalized
     *
     * @return float|null Returns NULL on error.
     */
    public function project(GEOSGeometry $other, $normalized = false) {}

    /**
     * @param float   $dist
     * @param boolean $normalized
     *
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function interpolate($dist, $normalized = false) {}

    /**
     * @param float $dist
     * @param array $styleArray
     *
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function buffer($dist, array $styleArray = []) {}

    /**
     * @param float $dist
     * @param array $styleArray
     *
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function offsetCurve($dist, array $styleArray = []) {}

    /**
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function envelope() {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function intersection(GEOSGeometry $geom) {}

    /**
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function convexHull() {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function difference(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function symDifference(GEOSGeometry $geom) {}

    /**
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function boundary() {}

    /**
     * @param GEOSGeometry $otherGeom Optional, but explicit NULL is not allowed.
     *
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function union(GEOSGeometry $otherGeom = null) {}

    /**
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function pointOnSurface() {}

    /**
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function centroid() {}

    /**
     * @param GEOSGeometry $otherGeom
     * @param string       $pattern
     *
     * @return string|boolean|null String if pattern is omitted, boolean if pattern is set, NULL on error.
     */
    public function relate(GEOSGeometry $otherGeom, $pattern = '') {}

    /**
     * @param GEOSGeometry $otherGeom
     * @param integer      $rule
     *
     * @return string|null Returns NULL on error.
     */
    public function relateBoundaryNodeRule(GEOSGeometry $otherGeom, $rule) {}

    /**
     * @param float   $tolerance
     * @param boolean $preserveTopology
     *
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function simplify($tolerance, $preserveTopology = false) {}

    /**
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function normalize() {}

    /**
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function extractUniquePoints() {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return boolean|null Returns NULL on error.
     */
    public function disjoint(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return boolean|null Returns NULL on error.
     */
    public function touches(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return boolean|null Returns NULL on error.
     */
    public function intersects(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return boolean|null Returns NULL on error.
     */
    public function crosses(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return boolean|null Returns NULL on error.
     */
    public function within(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return boolean|null Returns NULL on error.
     */
    public function contains(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return boolean|null Returns NULL on error.
     */
    public function overlaps(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return boolean|null Returns NULL on error.
     */
    public function covers(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return boolean|null Returns NULL on error.
     */
    public function coveredBy(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return boolean|null Returns NULL on error.
     */
    public function equals(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     * @param float        $tolerance
     *
     * @return boolean|null Returns NULL on error.
     */
    public function equalsExact(GEOSGeometry $geom, $tolerance = 0.0) {}

    /**
     * @return boolean|null Returns NULL on error.
     */
    public function isEmpty() {}

    /**
     * @return array|null Returns NULL on error.
     */
    public function checkValidity() {}

    /**
     * @return boolean|null Returns NULL on error.
     */
    public function isSimple() {}

    /**
     * @return boolean|null Returns NULL on error.
     */
    public function isRing() {}

    /**
     * @return boolean|null Returns NULL on error.
     */
    public function hasZ() {}

    /**
     * @return boolean|null Returns NULL on error.
     */
    public function isClosed() {}

    /**
     * @return string|null Returns NULL on error.
     */
    public function typeName() {}

    /**
     * @return integer|null Returns NULL on error.
     */
    public function typeId() {}

    /**
     * @return integer
     */
    public function getSRID() {}

    /**
     * @param integer $srid
     *
     * @return void
     */
    public function setSRID($srid) {}

    /**
     * @return integer|null Returns NULL on error.
     */
    public function numGeometries() {}

    /**
     * @param integer $num
     *
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function geometryN($num) {}

    /**
     * @return integer|null Returns NULL on error.
     */
    public function numInteriorRings() {}

    /**
     * @return integer|null Returns NULL on error.
     */
    public function numPoints() {}

    /**
     * @return float|null Returns NULL on error.
     */
    public function getX() {}

    /**
     * @return float|null Returns NULL on error.
     */
    public function getY() {}

    /**
     * @param integer $num
     *
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function interiorRingN($num) {}

    /**
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function exteriorRing() {}

    /**
     * @return integer|null Returns NULL on error.
     */
    public function numCoordinates() {}

    /**
     * @return integer|null Returns NULL on error.
     */
    public function dimension() {}

    /**
     * @return integer|null Returns NULL on error.
     */
    public function coordinateDimension() {}

    /**
     * @param integer $num
     *
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function pointN($num) {}

    /**
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function startPoint() {}

    /**
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function endPoint() {}

    /**
     * @return float|null Returns NULL on error.
     */
    public function area() {}

    /**
     * @return float|null Returns NULL on error.
     */
    public function length() {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return float|null Returns NULL on error.
     */
    public function distance(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return float|null Returns NULL on error.
     */
    public function hausdorffDistance(GEOSGeometry $geom) {}

    /**
     * @param GEOSGeometry $geom
     * @param float        $tolerance
     *
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function snapTo(GEOSGeometry $geom, $tolerance) {}

    /**
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function node() {}

    /**
     * @param float   $tolerance Snapping tolerance to use for improved robustness.
     * @param boolean $onlyEdges If true will return a MULTILINESTRING, otherwise (the default)
     *                           it will return a GEOMETRYCOLLECTION containing triangular POLYGONs.
     *
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function delaunayTriangulation($tolerance = 0.0, $onlyEdges = false) {}

    /**
     * @param float        $tolerance Snapping tolerance to use for improved robustness.
     * @param boolean      $onlyEdges If true will return a MULTILINESTRING, otherwise (the default)
     *                                it will return a GEOMETRYCOLLECTION containing POLYGONs.
     * @param GEOSGeometry $extent    Clip returned diagram by the extent of the given geometry.
     *                                Optional, but explicit NULL value is not allowed.
     *
     * @return GEOSGeometry|null Returns NULL on error.
     */
    public function voronoiDiagram($tolerance = 0.0, $onlyEdges = false, GEOSGeometry $extent = null) {}
}
