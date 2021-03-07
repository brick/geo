<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Boundary() function.
 */
class BoundaryFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_Boundary';
    }

    protected function getParameterCount() : int
    {
        return 1;
    }
}
