<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Centroid() function.
 */
class CentroidFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_Centroid';
    }

    protected function getParameterCount() : int
    {
        return 1;
    }
}
