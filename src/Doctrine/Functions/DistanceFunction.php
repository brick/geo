<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Distance() function.
 */
class DistanceFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Distance';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 2;
    }
}
