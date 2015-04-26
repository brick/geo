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
    protected function getSqlFunctionName()
    {
        return 'ST_Contains';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 2;
    }
}
