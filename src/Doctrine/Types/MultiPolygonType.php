<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Types;

use Brick\Geo\Proxy\MultiPolygonProxy;

/**
 * Doctrine type for MultiPolygon.
 */
class MultiPolygonType extends GeometryType
{
    public function getName()
    {
        return 'MultiPolygon';
    }

    protected function getProxyClassName() : string
    {
        return MultiPolygonProxy::class;
    }
}
