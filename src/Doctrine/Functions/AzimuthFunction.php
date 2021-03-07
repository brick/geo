<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Azimuth() function.
 */
class AzimuthFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_Azimuth';
    }

    protected function getParameterCount() : int
    {
        return 2;
    }
}
