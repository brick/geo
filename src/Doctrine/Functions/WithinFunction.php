<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Within() function.
 */
class WithinFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_Within';
    }

    protected function getParameterCount() : int
    {
        return 2;
    }
}
