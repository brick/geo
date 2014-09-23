<?php

namespace Brick\Doctrine\Types\Geometry;

use Brick\Geo\Proxy\PointProxy;

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
        return GeometryType::POINT;
    }

    /**
     * {@inheritdoc}
     */
    protected function createGeometryProxy($wkb)
    {
        return new PointProxy($wkb, true);
    }
}
