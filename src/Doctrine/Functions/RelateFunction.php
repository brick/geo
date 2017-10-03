<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Relate() function.
 */
class RelateFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Relate';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 3;
    }
}
