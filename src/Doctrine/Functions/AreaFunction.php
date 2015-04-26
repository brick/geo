<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Area() function.
 */
class AreaFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName()
    {
        return 'ST_Area';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 1;
    }
}
