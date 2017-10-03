<?php

/**
 * GEOSWKTWriter class stub.
 *
 * These stubs are required for IDEs to provide autocompletion and static code analysis during development.
 * They are not required for production.
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
     * @return string
     *
     * @throws \Exception
     */
    public function write(GEOSGeometry $geom) {}

    /**
     * @param bool $trim
     *
     * @return void
     */
    public function setTrim($trim) {}

    /**
     * @param int $prec
     *
     * @return void
     */
    public function setRoundingPrecision($prec) {}

    /**
     * @param int $dim
     *
     * @return void
     */
    public function setOutputDimension($dim) {}

    /**
     * @return int
     */
    public function getOutputDimension() {}

    /**
     * @param bool $val
     *
     * @return void
     */
    public function setOld3D($val) {}
}
