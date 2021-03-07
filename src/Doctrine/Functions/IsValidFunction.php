<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * IsValid() function.
 */
class IsValidFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_IsValid';
    }

    protected function getParameterCount() : int
    {
        return 1;
    }
}
