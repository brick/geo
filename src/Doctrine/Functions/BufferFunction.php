<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Buffer() function.
 */
class BufferFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Buffer';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 2;
    }
}
