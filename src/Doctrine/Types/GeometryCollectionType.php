<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Types;

use Brick\Geo\Proxy\GeometryCollectionProxy;

/**
 * Doctrine type for GeometryCollection.
 */
class GeometryCollectionType extends GeometryType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'GeometryCollection';
    }

    /**
     * {@inheritdoc}
     */
    protected function getProxyClassName() : string
    {
        return GeometryCollectionProxy::class;
    }
}
