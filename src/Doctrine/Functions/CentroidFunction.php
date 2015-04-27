<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Centroid() function.
 */
class CentroidFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName()
    {
        return 'ST_Centroid';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 1;
    }
}
