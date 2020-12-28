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

    public function __toString(): string {}

    /**
     * @throws \Exception
     */
    public function project(GEOSGeometry $other, bool $normalized = false): float {}

    /**
     * @throws \Exception
     */
    public function interpolate(float $dist, bool $normalized = false): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function buffer(float $dist, array $styleArray = []): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function offsetCurve(float $dist, array $styleArray = []): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function envelope(): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function intersection(GEOSGeometry $geom): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function convexHull(): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function difference(GEOSGeometry $geom): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function symDifference(GEOSGeometry $geom): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function boundary(): GEOSGeometry {}

    /**
     * @param GEOSGeometry $otherGeom Optional, but explicit NULL is not allowed.
     *
     * @throws \Exception
     */
    public function union(GEOSGeometry $otherGeom = null): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function pointOnSurface(): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function centroid(): GEOSGeometry {}

    /**
     * @return string|bool String if pattern is omitted, boolean if pattern is set.
     *
     * @throws \Exception
     */
    public function relate(GEOSGeometry $otherGeom, string $pattern = '') {}

    /**
     * @throws \Exception
     */
    public function relateBoundaryNodeRule(GEOSGeometry $otherGeom, int $rule): string {}

    /**
     * @throws \Exception
     */
    public function simplify(float $tolerance, bool $preserveTopology = false): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function normalize(): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function extractUniquePoints(): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function disjoint(GEOSGeometry $geom): bool {}

    /**
     * @throws \Exception
     */
    public function touches(GEOSGeometry $geom): bool {}

    /**
     * @throws \Exception
     */
    public function intersects(GEOSGeometry $geom): bool {}

    /**
     * @throws \Exception
     */
    public function crosses(GEOSGeometry $geom): bool {}

    /**
     * @throws \Exception
     */
    public function within(GEOSGeometry $geom): bool {}

    /**
     * @throws \Exception
     */
    public function contains(GEOSGeometry $geom): bool {}

    /**
     * @throws \Exception
     */
    public function overlaps(GEOSGeometry $geom): bool {}

    /**
     * @throws \Exception
     */
    public function covers(GEOSGeometry $geom): bool {}

    /**
     * @throws \Exception
     */
    public function coveredBy(GEOSGeometry $geom): bool {}

    /**
     * @throws \Exception
     */
    public function equals(GEOSGeometry $geom): bool {}

    /**
     * @throws \Exception
     */
    public function equalsExact(GEOSGeometry $geom, float $tolerance = 0.0): bool {}

    /**
     * @throws \Exception
     */
    public function isEmpty(): bool {}

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
     * @psalm-return array{valid: bool, reason?: string, location?: GEOSGeometry}
     *
     * @throws \Exception
     */
    public function checkValidity(): array {}

    /**
     * @throws \Exception
     */
    public function isSimple(): bool {}

    /**
     * @throws \Exception
     */
    public function isRing(): bool {}

    /**
     * @throws \Exception
     */
    public function hasZ(): bool {}

    /**
     * @throws \Exception On error, e.g. if this geometry is not a LineString.
     */
    public function isClosed(): bool {}

    /**
     * @throws \Exception
     */
    public function typeName(): string {}

    /**
     * @throws \Exception
     */
    public function typeId(): int {}

    /**
     * @throws \Exception
     */
    public function getSRID(): int {}

    /**
     * @throws \Exception
     */
    public function setSRID(int $srid): void {}

    /**
     * @throws \Exception
     */
    public function numGeometries(): int {}

    /**
     * @throws \Exception
     */
    public function geometryN(int $num): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function numInteriorRings(): int {}

    /**
     * @throws \Exception
     */
    public function numPoints(): int {}

    /**
     * @throws \Exception
     */
    public function getX(): float {}

    /**
     * @throws \Exception
     */
    public function getY(): float {}

    /**
     * @throws \Exception
     */
    public function interiorRingN(int $num): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function exteriorRing(): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function numCoordinates(): int {}

    /**
     * @throws \Exception
     */
    public function dimension(): int {}

    /**
     * @throws \Exception
     */
    public function coordinateDimension(): int {}

    /**
     * @throws \Exception
     */
    public function pointN(int $num): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function startPoint(): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function endPoint(): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function area(): float {}

    /**
     * @throws \Exception
     */
    public function length(): float {}

    /**
     * @throws \Exception
     */
    public function distance(GEOSGeometry $geom): float {}

    /**
     * @throws \Exception
     */
    public function hausdorffDistance(GEOSGeometry $geom): float {}

    /**
     * @throws \Exception
     */
    public function snapTo(GEOSGeometry $geom, float $tolerance): GEOSGeometry {}

    /**
     * @throws \Exception
     */
    public function node(): GEOSGeometry {}

    /**
     * @param float $tolerance Snapping tolerance to use for improved robustness.
     * @param bool  $onlyEdges If true will return a MULTILINESTRING, otherwise (the default)
     *                         it will return a GEOMETRYCOLLECTION containing triangular POLYGONs.
     *
     * @throws \Exception
     */
    public function delaunayTriangulation(float $tolerance = 0.0, bool $onlyEdges = false): GEOSGeometry {}

    /**
     * @param float             $tolerance Snapping tolerance to use for improved robustness.
     * @param bool              $onlyEdges If true will return a MULTILINESTRING, otherwise (the default)
     *                                     it will return a GEOMETRYCOLLECTION containing POLYGONs.
     * @param GEOSGeometry|null $extent    Clip returned diagram by the extent of the given geometry.
     *                                     Optional, but explicit NULL value is not allowed.
     *
     * @throws \Exception
     */
    public function voronoiDiagram(float $tolerance = 0.0, bool $onlyEdges = false, GEOSGeometry $extent = null): GEOSGeometry {}
}
