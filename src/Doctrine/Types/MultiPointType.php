<?php

namespace Brick\Doctrine\Types\Geometry;

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
        return GeometryType::MULTIPOINT;
    }

    /**
     * {@inheritdoc}
     */
    protected function createGeometryProxy($wkb)
    {
        return new MultiPointProxy($wkb, true);
    }
}
