<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Relate() function.
 */
class RelateFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_Relate';
    }

    protected function getParameterCount() : int
    {
        return 3;
    }
}
