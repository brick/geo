<?php

namespace Brick\Geo;

/**
 * A LinearRing is a LineString which is both closed & simple.
 */
class LinearRing extends LineString
{
    /**
     * Builds a LinearRing from an array of Point objects
     *
     * @param  Point[]           $points
     * @return LinearRing
     * @throws GeometryException
     */
    public static function factory(array $points)
    {
        $linearRing = new LinearRing($points);

        if (! $linearRing->isRing()) {
            throw new GeometryException('A LinearRing must be closed & simple');
        }

        return $linearRing;
    }

    /**
     * {@inheritdoc}
     *
     * @todo find out if LinearRing should exist as a class, and if geometryType() should return LinearRing.
     */
    // public function geometryType()
    // {
    //     return 'LinearRing';
    // }
}
