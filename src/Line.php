<?php

namespace Brick\Geo;

/**
 * A Line is a LineString composed of exactly two points.
 */
class Line extends LineString
{
    /**
     * Builds a Line from an array of Point objects
     *
     * @param  Point[]           $points
     * @return Line
     * @throws GeometryException
     */
    public static function factory(array $points)
    {
        $line = parent::factory($points);

        if (! $line instanceof Line) {
            throw new GeometryException('The Point(s) provided do not form a Line');
        }

        return $line;
    }

    /**
     * Builds a Line from two Point objects
     *
     * @param  Point $p1
     * @param  Point $p2
     * @return Line
     */
    public static function create(Point $p1, Point $p2)
    {
        return self::factory([$p1, $p2]);
    }

    /**
     * {@inheritdoc}
     * A Point is a simple Geometry.
     */
    public function isSimple()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * A Point is a non-empty Geometry.
     */
    public function isEmpty()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @todo find out if LinearRing should exist as a class, and if geometryType() should return LinearRing.
     */
    // public function geometryType()
    // {
    //     return 'Line';
    // }
}
