<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Difference() function.
 */
class DifferenceFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_Difference';
    }

    protected function getParameterCount() : int
    {
        return 2;
    }
}
