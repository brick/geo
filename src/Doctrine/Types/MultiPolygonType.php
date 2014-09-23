<?php

namespace Brick\Doctrine\Types\Geometry;

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
    protected function createGeometryProxy($wkb)
    {
        return new MultiPolygonProxy($wkb, true);
    }
}
