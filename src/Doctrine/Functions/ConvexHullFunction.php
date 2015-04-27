<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * ConvexHull() function.
 */
class ConvexHullFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName()
    {
        return 'ST_ConvexHull';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 1;
    }
}
