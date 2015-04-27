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
    protected function getSqlFunctionName()
    {
        return 'ST_Disjoint';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 2;
    }
}
