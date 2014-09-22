<?php

/**
 * GEOSWKBReader class stub.
 *
 * @see https://github.com/libgeos/libgeos/blob/svn-trunk/php/geos.c
 */
class GEOSWKBReader
{
    /**
     * Constructor.
     */
    public function __construct() {}

    /**
     * Reads a geometry out of the given hex-encoded WKB.
     *
     * @param string $wkb
     *
     * @return GEOSGeometry|null The geometry, or NULL on failure.
     */
    public function readHEX($wkb) {}
}
