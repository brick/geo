<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

/**
 * Represents an (E)WKT token.
 */
enum WKTTokenType
{
    case SRID; // EWKT only
    case Word;
    case Number;
    case Other;
}
