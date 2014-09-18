<?php

/**
 * GEOSWKTWriter class stub.
 *
 * @see https://github.com/libgeos/libgeos/blob/svn-trunk/php/geos.c
 */
class GEOSWKTWriter
{
    /**
     * Constructor.
     */
    public function __construct() {}

    /**
     * @param GEOSGeometry $geom
     *
     * @return string|null The WKT, or NULL on failure.
     */
    public function write(GEOSGeometry $geom) {}

    /**
     * @param boolean $trim
     *
     * @return void
     */
    public function setTrim($trim) {}

    /**
     * @param integer $prec
     *
     * @return void
     */
    public function setRoundingPrecision($prec) {}

    /**
     * @param integer $dim
     *
     * @return void
     */
    public function setOutputDimension($dim) {}

    /**
     * @return integer
     */
    public function getOutputDimension() {}

    /**
     * @param boolean $val
     *
     * @return void
     */
    public function setOld3D($val) {}
}
