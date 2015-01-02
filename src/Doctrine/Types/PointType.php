<?php

namespace Brick\Geo\Doctrine\Types;

use Brick\Geo\Proxy\PointProxy;

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Doctrine type for Point.
 */
class PointType extends GeometryType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Point';
    }

    /**
     * {@inheritdoc}
     */
    protected function createGeometryProxy($wkb)
    {
        return new PointProxy($wkb, true);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
