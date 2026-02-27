<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * GeographyFromText() function.
 */
class GeographyFromTextFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_GeographyFromText';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 1;
    }
}
