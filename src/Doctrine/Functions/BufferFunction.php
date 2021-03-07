<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Buffer() function.
 */
class BufferFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_Buffer';
    }

    protected function getParameterCount() : int
    {
        return 2;
    }
}
