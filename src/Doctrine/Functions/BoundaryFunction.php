<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Boundary() function.
 */
class BoundaryFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Boundary';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 1;
    }
}
