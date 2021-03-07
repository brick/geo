<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * IsClosed() function.
 */
class IsClosedFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_IsClosed';
    }

    protected function getParameterCount() : int
    {
        return 1;
    }
}
