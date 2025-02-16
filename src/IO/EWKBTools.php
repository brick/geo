<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

/**
 * Tools for EWKB classes.
 */
final class EWKBTools extends WKBTools
{
    final public const int Z = 0x80000000;
    final public const int M = 0x40000000;
    final public const int S = 0x20000000;
}
