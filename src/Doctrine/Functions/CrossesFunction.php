<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Crosses() function.
 */
class CrossesFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Crosses';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 2;
    }
}
