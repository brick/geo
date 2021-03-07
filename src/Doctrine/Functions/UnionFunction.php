<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Functions;

/**
 * Union() function.
 */
class UnionFunction extends AbstractFunction
{
    protected function getSqlFunctionName() : string
    {
        return 'ST_Union';
    }

    protected function getParameterCount() : int
    {
        return 2;
    }
}
