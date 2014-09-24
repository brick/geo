<?php

namespace Brick\Geo;

/**
 * A Line is a LineString with exactly 2 Points.
 */
class Line extends LineString
{
    /**
     * Builds a Line from two Point objects
     *
     * @param Point $p1
     * @param Point $p2
     *
     * @return Line
     */
    public static function create(Point $p1, Point $p2)
    {
        return self::factory([$p1, $p2]);
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function isSimple()
    {
        return true;
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
     public function geometryType()
     {
         return 'Line';
     }
}
