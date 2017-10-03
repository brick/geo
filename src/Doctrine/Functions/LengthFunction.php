<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Length() function.
 */
class LengthFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Length';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 1;
    }
}
