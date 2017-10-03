<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Contains() function.
 */
class ContainsFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Contains';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 2;
    }
}
