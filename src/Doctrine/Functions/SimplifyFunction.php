<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Simplify() function.
 */
class SimplifyFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName()
    {
        return 'ST_Simplify';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 2;
    }
}
