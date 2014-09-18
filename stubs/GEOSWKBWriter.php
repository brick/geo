<?php

/**
 * GEOSWKBWriter class stub.
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
     * @return integer
     */
    public function getOutputDimension() {}

    /**
     * @param integer $dim
     *
     * @return void
     */
    public function setOutputDimension($dim) {}

    /**
     * @return integer
     */
    public function getByteOrder() {}

    /**
     * @param integer $dim
     *
     * @return void
     */
    public function setByteOrder($dim) {}

    /**
     * @return boolean
     */
    public function getIncludeSRID() {}

    /**
     * @param boolean $inc
     *
     * @return void
     */
    public function setIncludeSRID($inc) {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return string|null The WKB, or NULL on failure.
     */
    public function writeHEX(GEOSGeometry $geom) {}
}
