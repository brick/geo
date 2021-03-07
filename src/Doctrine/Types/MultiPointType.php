<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Types;

use Brick\Geo\Proxy\MultiPointProxy;

/**
 * Doctrine type for MultiPoint.
 */
class MultiPointType extends GeometryType
{
    public function getName()
    {
        return 'MultiPoint';
    }

    protected function getProxyClassName() : string
    {
        return MultiPointProxy::class;
    }

    protected function hasKnownSubclasses() : bool
    {
        return false;
    }
}
