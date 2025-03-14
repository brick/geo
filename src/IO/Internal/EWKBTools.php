<?php

declare(strict_types=1);

namespace Brick\Geo\IO\Internal;

/**
 * Tools for EWKB classes.
 *
 * @internal
 */
final class EWKBTools extends WKBTools
{
    final public const Z = 0x80000000;
    final public const M = 0x40000000;
    final public const S = 0x20000000;
}
