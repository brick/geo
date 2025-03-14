<?php

declare(strict_types=1);

namespace Brick\Geo\IO\Internal;

/**
 * Represents an (E)WKT token type.
 *
 * @internal
 */
enum WKTTokenType
{
    case SRID; // EWKT only
    case Word;
    case Number;
    case Other;
}
