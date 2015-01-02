<?php

namespace Brick\Geo\Doctrine\Types;

use Brick\Geo\Proxy\MultiPointProxy;

use Doctrine\DBAL\Platforms\AbstractPlatform;

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
    protected function createGeometryProxy($wkb)
    {
        return new MultiPointProxy($wkb, true);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
