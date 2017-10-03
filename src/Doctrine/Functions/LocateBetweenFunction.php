<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * LocateBetween() function.
 */
class LocateBetweenFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_LocateBetween';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 3;
    }
}
