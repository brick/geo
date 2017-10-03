<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Touches() function.
 */
class TouchesFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Touches';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 2;
    }
}
