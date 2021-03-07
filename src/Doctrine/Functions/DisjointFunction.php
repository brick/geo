<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Disjoint() function.
 */
class DisjointFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_Disjoint';
    }

    protected function getParameterCount() : int
    {
        return 2;
    }
}
