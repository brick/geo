<?php

namespace Brick\Geo\Doctrine\Types;

use Brick\Geo\Proxy\PolygonProxy;

/**
 * Doctrine type for Polygon.
 */
class PolygonType extends GeometryType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Polygon';
    }

    /**
     * {@inheritdoc}
     */
    protected function getProxyClassName()
    {
        return PolygonProxy::class;
    }
}
