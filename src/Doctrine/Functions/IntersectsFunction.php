<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Intersects() function.
 */
class IntersectsFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_Intersects';
    }

    protected function getParameterCount() : int
    {
        return 2;
    }
}
