<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Area() function.
 */
class AreaFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Area';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 1;
    }
}
