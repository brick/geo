<?php

/**
 * GEOSWKBWriter class stub.
 *
 * These stubs are required for IDEs to provide autocompletion and static code analysis during development.
 * They are not required for production.
 *
 * @see https://github.com/libgeos/libgeos/blob/svn-trunk/php/geos.c
 */
class GEOSWKBWriter
{
    /**
     * Constructor.
     */
    public function __construct() {}

    /**
     * Returns the output dimension.
     *
     * @return int 2 or 2D, 3 for 3D.
     */
    public function getOutputDimension(): int {}

    /**
     * Sets the output dimension.
     *
     * @param int $dim 2 or 2D, 3 for 3D.
     */
    public function setOutputDimension(int $dim): void {}

    /**
     * Returns the output WKB byte order.
     *
     * @return int 0 for BIG endian, 1 for LITTLE endian.
     */
    public function getByteOrder(): int {}

    /**
     * Sets the output WKB byte order.
     *
     * @param int $byteOrder 0 for BIG endian, 1 for LITTLE endian.
     */
    public function setByteOrder(int $byteOrder): void {}

    /**
     * Returns whether the output includes SRID.
     */
    public function getIncludeSRID(): bool {}

    /**
     * Sets whether the output includes SRID.
     */
    public function setIncludeSRID(bool $inc): void {}

    /**
     * Writes the given geometry as WKB.
     *
     * @since 3.5.0
     *
     * @throws \Exception If the geometry does not have a WKB representation, notably POINT EMPTY.
     */
    public function write(GEOSGeometry $geom): string {}

    /**
     * Writes the given geometry as hex-encoded WKB.
     *
     * @throws \Exception If the geometry does not have a WKB representation, notably POINT EMPTY.
     */
    public function writeHEX(GEOSGeometry $geom): string {}
}
