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
    protected function getSqlFunctionName()
    {
        return 'ST_Distance';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 2;
    }
}
