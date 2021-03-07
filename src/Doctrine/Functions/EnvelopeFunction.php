<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Envelope() function.
 */
class EnvelopeFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_Envelope';
    }

    protected function getParameterCount() : int
    {
        return 1;
    }
}
