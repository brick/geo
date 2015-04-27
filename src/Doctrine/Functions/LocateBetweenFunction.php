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
    protected function getSqlFunctionName()
    {
        return 'ST_LocateBetween';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 3;
    }
}
