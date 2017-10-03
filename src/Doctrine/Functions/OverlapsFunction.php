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
    protected function getSqlFunctionName() : string
    {
        return 'ST_Overlaps';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 2;
    }
}
