<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * IsSimple() function.
 */
class IsSimpleFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName()
    {
        return 'ST_IsSimple';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 1;
    }
}
