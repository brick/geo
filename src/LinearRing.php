<?php

namespace Brick\Geo;

/**
 * A LinearRing is a LineString that is both closed and simple.
 */
class LinearRing extends LineString
{
    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
     public function geometryType()
     {
         return 'LinearRing';
     }
}
