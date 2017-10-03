<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Equals() function.
 */
class EqualsFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Equals';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 2;
    }
}
