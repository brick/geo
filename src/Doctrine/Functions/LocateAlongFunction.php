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
    protected function getSqlFunctionName() : string
    {
        return 'ST_LocateAlong';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 2;
    }
}
