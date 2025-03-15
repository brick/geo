<?php

declare(strict_types=1);

namespace Brick\Geo\Io\Internal;

/**
 * Represents an (E)WKT token type.
 *
 * @internal
 */
enum WktTokenType
{
    case SRID; // EWKT only
    case Word;
    case Number;
    case Other;
}
