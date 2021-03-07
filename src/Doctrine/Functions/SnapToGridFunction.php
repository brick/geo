<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * SnapToGrid() function.
 */
class SnapToGridFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_SnapToGrid';
    }

    protected function getParameterCount() : int
    {
        return 2;
    }
}
