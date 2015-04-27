<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * LocateAlong() function.
 */
class LocateAlongFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName()
    {
        return 'ST_LocateAlong';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 2;
    }
}
