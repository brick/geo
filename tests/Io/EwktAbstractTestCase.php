<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Io;

/**
 * Base class for EWKT tests.
 */
abstract class EwktAbstractTestCase extends WktAbstractTestCase
{
    /**
     * Prepends the SRID to a WKT string, making it an EWKT.
     */
    final protected static function toEwkt(string $wkt, int $srid) : string
    {
        return 'SRID=' . $srid . ';' . $wkt;
    }
}
