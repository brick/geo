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
    protected function getSqlFunctionName()
    {
        return 'ST_Boundary';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 1;
    }
}
