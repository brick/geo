<?php

namespace Brick\Geo\Doctrine\Types;

use Brick\Geo\Proxy\MultiPolygonProxy;

/**
 * Doctrine type for MultiPolygon.
 */
class MultiPolygonType extends GeometryType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'MultiPolygon';
    }

    /**
     * {@inheritdoc}
     */
    protected function getProxyClassName() : string
    {
        return MultiPolygonProxy::class;
    }
}
