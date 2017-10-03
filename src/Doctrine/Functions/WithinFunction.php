<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Within() function.
 */
class WithinFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Within';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 2;
    }
}
