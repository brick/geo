<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Simplify() function.
 */
class SimplifyFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Simplify';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 2;
    }
}
