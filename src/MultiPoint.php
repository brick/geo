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
     * Builds a MultiPoint from an array of Point objects
     *
     * @param Point[] $points
     *
     * @return MultiPoint
     *
     * @throws GeometryException
     */
    public static function factory(array $points)
    {
        foreach ($points as $point) {
            if (! $point instanceof Point) {
                throw new GeometryException('A MultiPoint can only contain Point objects');
            }
        }

        return new MultiPoint($points);
    }

    /**
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'MultiPoint';
    }
}
