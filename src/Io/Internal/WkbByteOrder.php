<?php

declare(strict_types=1);

namespace Brick\Geo\Io\Internal;

/**
 * @internal
 */
enum WkbByteOrder: int
{
    case BIG_ENDIAN = 0;
    case LITTLE_ENDIAN = 1;
}
