<?php

namespace Brick\Doctrine\Types\Geometry;

use Brick\Geo\Polygon;

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
    protected static function convertFromWkb($wkb)
    {
        return Polygon::fromBinary($wkb);
    }
}
