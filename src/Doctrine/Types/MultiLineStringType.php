<?php

namespace Brick\Geo\Doctrine\Types;

use Brick\Geo\Proxy\MultiLineStringProxy;

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Doctrine type for MultiLineString.
 */
class MultiLineStringType extends GeometryType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'MultiLineString';
    }

    /**
     * {@inheritdoc}
     */
    protected function createGeometryProxy($wkb)
    {
        return new MultiLineStringProxy($wkb, true);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
