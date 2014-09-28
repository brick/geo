<?php

namespace Brick\Geo\IO;

/**
 * Tools for EWKB classes.
 */
class EWKBTools extends WKBTools
{
    const Z = 0x80000000;
    const M = 0x40000000;
    const S = 0x20000000;
}
