<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Intersects() function.
 */
class IntersectsFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName()
    {
        return 'ST_Intersects';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 2;
    }
}
