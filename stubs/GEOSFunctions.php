<?php

/**
 * GEOS functions stubs.
 *
 * @see https://github.com/libgeos/libgeos/blob/svn-trunk/php/geos.c
 */

/**
 * @return string
 */
function GEOSVersion() {}

/**
 * The returned array contains the following elements:
 *
 *  - 'rings'
 *      Type: array of GEOSGeometry
 *      Rings that can be formed by the costituent
 *      linework of geometry.
 *  - 'cut_edges' (optional)
 *      Type: array of GEOSGeometry
 *      Edges which are connected at both ends but
 *      which do not form part of polygon.
 *  - 'dangles'
 *      Type: array of GEOSGeometry
 *      Edges which have one or both ends which are
 *      not incident on another edge endpoint
 *  - 'invalid_rings'
 *      Type: array of GEOSGeometry
 *      Edges which form rings which are invalid
 *      (e.g. the component lines contain a self-intersection)
 *
 * @param GEOSGeometry $geom
 *
 * @return array
 */
function GEOSPolygonize(GEOSGeometry $geom) {}

/**
 * @param GEOSGeometry $geom
 *
 * @return array
 */
function GEOSLineMerge(GEOSGeometry $geom) {}

/**
 * @param GEOSGeometry $geom1
 * @param GEOSGeometry $geom2
 *
 * @return GEOSGeometry
 */
function GEOSSharedPaths(GEOSGeometry $geom1, GEOSGeometry $geom2) {}

/**
 * @param string $matrix
 * @param string $pattern
 *
 * @return boolean
 */
function GEOSRelateMatch($matrix, $pattern) {}
