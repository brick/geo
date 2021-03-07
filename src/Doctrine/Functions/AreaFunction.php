<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Area() function.
 */
class AreaFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_Area';
    }

    protected function getParameterCount() : int
    {
        return 1;
    }
}
