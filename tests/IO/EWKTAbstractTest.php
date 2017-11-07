<?php

namespace Brick\Geo\Tests\IO;

/**
 * Base class for EWKT tests.
 */
abstract class EWKTAbstractTest extends WKTAbstractTest
{
    /**
     * Prepends the SRID to a WKT string, making it an EWKT.
     *
     * @param string $wkt  The WKT.
     * @param int    $srid The SRID.
     *
     * @return string The EWKT.
     */
    protected function toEWKT(string $wkt, int $srid) : string
    {
        return 'SRID=' . $srid . ';' . $wkt;
    }
}
