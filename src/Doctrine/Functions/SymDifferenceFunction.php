<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * SymDifference() function.
 */
class SymDifferenceFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName()
    {
        return 'ST_SymDifference';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 2;
    }
}
