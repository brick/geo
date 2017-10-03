<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Difference() function.
 */
class DifferenceFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Difference';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 2;
    }
}
