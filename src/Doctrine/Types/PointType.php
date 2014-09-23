<?php

namespace Brick\Doctrine\Types\Geometry;

use Brick\Geo\Point;

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
    protected static function convertFromWkb($wkb)
    {
        return Point::fromBinary($wkb);
    }
}
