<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * PointOnSurface() function.
 */
class PointOnSurfaceFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_PointOnSurface';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 1;
    }
}
