<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Relate() function.
 */
class RelateFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName()
    {
        return 'ST_Relate';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 3;
    }
}
