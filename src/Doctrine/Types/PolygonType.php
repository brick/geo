<?php

namespace Brick\Doctrine\Types\Geometry;

use Brick\Geo\Proxy\PolygonProxy;

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
        return GeometryType::POLYGON;
    }

    /**
     * {@inheritdoc}
     */
    protected function createGeometryProxy($wkb)
    {
        return new PolygonProxy($wkb, true);
    }
}
