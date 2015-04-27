<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * SnapToGrid() function.
 */
class SnapToGridFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName()
    {
        return 'ST_SnapToGrid';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 2;
    }
}
