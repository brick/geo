<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * IsClosed() function.
 */
class IsClosedFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName()
    {
        return 'ST_IsClosed';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 1;
    }
}
