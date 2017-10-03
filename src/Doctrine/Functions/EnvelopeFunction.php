<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Envelope() function.
 */
class EnvelopeFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_Envelope';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 1;
    }
}
