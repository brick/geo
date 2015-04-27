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
    protected function getSqlFunctionName()
    {
        return 'ST_Touches';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 2;
    }
}
