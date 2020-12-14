<?php

/**
 * GEOS functions stubs.
 *
 * These stubs are required for IDEs to provide autocompletion and static code analysis during development.
 * They are not required for production.
 *
 * @see https://github.com/libgeos/libgeos/blob/svn-trunk/php/geos.c
 */

function GEOSVersion(): string {}

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
 */
function GEOSPolygonize(GEOSGeometry $geom): array {}

function GEOSLineMerge(GEOSGeometry $geom): array {}

function GEOSSharedPaths(GEOSGeometry $geom1, GEOSGeometry $geom2): GEOSGeometry {}

function GEOSRelateMatch(string $matrix, string $pattern): bool {}
