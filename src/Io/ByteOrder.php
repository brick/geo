<?php

declare(strict_types=1);

namespace Brick\Geo\Io;

enum ByteOrder: int
{
    case BigEndian = 0;
    case LittleEndian = 1;
}
