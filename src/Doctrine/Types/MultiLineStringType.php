<?php

namespace Brick\Doctrine\Types\Geometry;

use Brick\Geo\Proxy\MultiLineStringProxy;

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
}
