<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * MaxDistance() function.
 */
class MaxDistanceFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_MaxDistance';
    }

    protected function getParameterCount() : int
    {
        return 2;
    }
}
