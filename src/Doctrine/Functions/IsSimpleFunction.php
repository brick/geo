<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * IsSimple() function.
 */
class IsSimpleFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_IsSimple';
    }

    protected function getParameterCount() : int
    {
        return 1;
    }
}
