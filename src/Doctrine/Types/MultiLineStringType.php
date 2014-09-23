<?php

namespace Brick\Doctrine\Types\Geometry;

use Brick\Geo\MultiLineString;

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
        return GeometryType::MULTILINESTRING;
    }

    /**
     * {@inheritdoc}
     */
    protected static function convertFromWkb($wkb)
    {
        return MultiLineString::fromBinary($wkb);
    }
}
