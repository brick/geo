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
     * @throws \Exception
     */
    public function write(GEOSGeometry $geom): string {}

    public function setTrim(bool $trim): void {}

    public function setRoundingPrecision(int $prec): void {}

    public function setOutputDimension(int $dim): void {}

    public function getOutputDimension(): int {}

    public function setOld3D(bool $val): void {}
}
