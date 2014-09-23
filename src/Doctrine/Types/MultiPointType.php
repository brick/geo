<?php

namespace Brick\Doctrine\Types\Geometry;

use Brick\Geo\MultiPoint;

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
    protected static function convertFromWkb($wkb)
    {
        return MultiPoint::fromBinary($wkb);
    }
}
