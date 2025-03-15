<?php

declare(strict_types=1);

namespace Brick\Geo\Io\Internal;

/**
 * @internal
 */
enum WkbByteOrder: int
{
    case BigEndian = 0;
    case LittleEndian = 1;
}
