<?php

namespace Brick\Doctrine\Types\Geometry;

use Brick\Geo\LineString;

/**
 * Doctrine type for LineString.
 */
class LineStringType extends GeometryType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return GeometryType::LINESTRING;
    }

    /**
     * {@inheritdoc}
     */
    protected static function convertFromWkb($wkb)
    {
        return LineString::fromBinary($wkb);
    }
}
