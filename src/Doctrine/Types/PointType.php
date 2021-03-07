<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Types;

use Brick\Geo\Proxy\PointProxy;

/**
 * Doctrine type for Point.
 */
class PointType extends GeometryType
{
    public function getName()
    {
        return 'Point';
    }

    protected function getProxyClassName() : string
    {
        return PointProxy::class;
    }

    protected function hasKnownSubclasses() : bool
    {
        return false;
    }
}
