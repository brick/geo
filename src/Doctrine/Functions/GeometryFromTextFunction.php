<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * GeometryFromText() function.
 */
class GeometryFromTextFunction extends AbstractFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getSqlFunctionName() : string
    {
        return 'ST_GeometryFromText';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterCount() : int
    {
        return 1;
    }
}
