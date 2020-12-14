<?php

/**
 * GEOSWKBReader class stub.
 *
 * These stubs are required for IDEs to provide autocompletion and static code analysis during development.
 * They are not required for production.
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
     * Reads a geometry out of the given WKB.
     *
     * @since 3.5.0
     *
     * @throws \Exception If the WKB is not valid.
     */
    public function read(string $wkb): GEOSGeometry {}

    /**
     * Reads a geometry out of the given hex-encoded WKB.
     *
     * @throws \Exception If the WKB is not valid.
     */
    public function readHEX(string $wkb): GEOSGeometry {}
}
