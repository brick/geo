<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * MaxDistance() function.
 */
class MaxDistanceFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_MaxDistance';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 2;
    }
}
