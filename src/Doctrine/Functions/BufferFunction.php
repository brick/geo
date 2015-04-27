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
    protected function getSqlFunctionName()
    {
        return 'ST_Buffer';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 2;
    }
}
