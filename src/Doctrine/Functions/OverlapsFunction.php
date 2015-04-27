<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Overlaps() function.
 */
class OverlapsFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName()
    {
        return 'ST_Overlaps';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 2;
    }
}
