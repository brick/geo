<?php

namespace Brick\Geo\Doctrine\Types;

use Brick\Geo\Proxy\MultiPointProxy;

/**
 * Doctrine type for MultiPoint.
 */
class MultiPointType extends GeometryType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'MultiPoint';
    }

    /**
     * {@inheritdoc}
     */
    protected function getProxyClassName()
    {
        return MultiPointProxy::class;
    }
}
