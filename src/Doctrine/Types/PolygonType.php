<?php

namespace Brick\Geo\Doctrine\Types;

use Brick\Geo\Proxy\PolygonProxy;

use Doctrine\DBAL\Platforms\AbstractPlatform;

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
    protected function createGeometryProxy($wkb)
    {
        return new PolygonProxy($wkb, true);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
