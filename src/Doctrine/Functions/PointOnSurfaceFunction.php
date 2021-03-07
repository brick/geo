<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * PointOnSurface() function.
 */
class PointOnSurfaceFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_PointOnSurface';
    }

    protected function getParameterCount() : int
    {
        return 1;
    }
}
