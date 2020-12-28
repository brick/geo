<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

/**
 * Tools for EWKB classes.
 */
class EWKBTools extends WKBTools
{
    public const Z = 0x80000000;
    public const M = 0x40000000;
    public const S = 0x20000000;
}
