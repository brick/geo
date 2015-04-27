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
    protected function getSqlFunctionName()
    {
        return 'ST_IsValid';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 1;
    }
}
