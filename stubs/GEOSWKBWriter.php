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
     * Returns the output dimension.
     *
     * @return integer 2 or 2D, 3 for 3D.
     */
    public function getOutputDimension() {}

    /**
     * Sets the output dimension.
     *
     * @param integer $dim 2 or 2D, 3 for 3D.
     *
     * @return void
     */
    public function setOutputDimension($dim) {}

    /**
     * Returns the output WKB byte order.
     *
     * @return integer 0 for BIG endian, 1 for LITTLE endian.
     */
    public function getByteOrder() {}

    /**
     * Sets the output WKB byte order.
     *
     * @param integer $byteOrder 0 for BIG endian, 1 for LITTLE endian.
     *
     * @return void
     */
    public function setByteOrder($byteOrder) {}

    /**
     * Returns whether the output includes SRID.
     *
     * @return boolean
     */
    public function getIncludeSRID() {}

    /**
     * Sets whether the output includes SRID.
     *
     * @param boolean $inc
     *
     * @return void
     */
    public function setIncludeSRID($inc) {}

    /**
     * Writes the given geometry as hex-encoded WKB.
     *
     * @param GEOSGeometry $geom
     *
     * @return string|null The WKB, or NULL on failure.
     */
    public function writeHEX(GEOSGeometry $geom) {}
}
