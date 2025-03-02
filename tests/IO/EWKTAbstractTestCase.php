<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

/**
 * Base class for EWKT tests.
 */
abstract class EWKTAbstractTestCase extends WKTAbstractTestCase
{
    /**
     * Prepends the SRID to a WKT string, making it an EWKT.
     */
    final protected static function toEWKT(string $wkt, int $srid) : string
    {
        return 'SRID=' . $srid . ';' . $wkt;
    }
}
