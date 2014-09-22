<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;

/**
 * A Triangle is a Polygon with 3 distinct, non-collinear vertices and no interior boundary.
 */
class Triangle extends Polygon
{
    /**
     * Builds a Triangle from an array of LinearRing objects.
     *
     * @param LinearRing[] $rings
     *
     * @return Triangle
     *
     * @throws GeometryException
     */
    public static function factory(array $rings)
    {
        $triangle = parent::factory($rings);

        if (! $triangle instanceof Triangle) {
            throw new GeometryException('The LinearRing(s) provided do not form a Triangle');
        }

        return $triangle;
    }

    /**
     * Builds a Triangle from three Point objects.
     *
     * @param Point $p1
     * @param Point $p2
     * @param Point $p3
     *
     * @return Triangle
     */
    public static function create(Point $p1, Point $p2, Point $p3)
    {
        $linearRing = LinearRing::factory([$p1, $p2, $p3, $p1]);

        return self::factory([$linearRing]);
    }

    /**
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'Triangle';
    }
}
