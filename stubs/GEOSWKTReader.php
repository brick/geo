<?php

/**
 * GEOSWKTReader class stub.
 *
 * These stubs are required for IDEs to provide autocompletion and static code analysis during development.
 * They are not required for production.
 *
 * @see https://github.com/libgeos/libgeos/blob/svn-trunk/php/geos.c
 */
class GEOSWKTReader
{
    /**
     * Constructor.
     */
    public function __construct() {}

    /**
     * @throws \Exception If the WKT is not valid.
     */
    public function read(string $wkt): GEOSGeometry {}
}
