<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * IsValid() function.
 */
class IsValidFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_IsValid';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 1;
    }
}
