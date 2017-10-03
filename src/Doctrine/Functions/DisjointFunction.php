<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Disjoint() function.
 */
class DisjointFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Disjoint';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 2;
    }
}
