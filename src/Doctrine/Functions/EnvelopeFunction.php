<?php

namespace Brick\Geo\Doctrine\Functions;

/**
 * Envelope() function.
 */
class EnvelopeFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName()
    {
        return 'ST_Envelope';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount()
    {
        return 1;
    }
}
