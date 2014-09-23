<?php

namespace Brick\Doctrine\Types\Geometry;

use Brick\Geo\MultiPolygon;

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
        return GeometryType::MULTIPOLYGON;
    }

    /**
     * {@inheritdoc}
     */
    protected static function convertFromWkb($wkb)
    {
        return MultiPolygon::fromBinary($wkb);
    }
}
