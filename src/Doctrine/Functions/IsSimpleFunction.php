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
    protected function getSqlFunctionName() : string
    {
        return 'ST_IsSimple';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 1;
    }
}
