<?php

namespace Brick\Geo;

/**
 * A MultiPoint is a geometry collection composed of Point elements.
 *
 * The points are not connected or ordered in any way.
 */
class MultiPoint extends GeometryCollection
{
    /**
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'MultiPoint';
    }
}
