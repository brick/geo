<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;

/**
 * A Triangle is a Polygon with 3 distinct, non-collinear vertices and no interior boundary.
 */
class Triangle extends Polygon
{
    /**
     * @param LineString[] $rings
     * @param boolean      $is3D
     * @param boolean      $isMeasured
     * @param integer      $srid
     *
     * @return static
     *
     * @throws GeometryException
     */
    public static function create(array $rings, $is3D, $isMeasured, $srid = 0)
    {
        $triangle = parent::create($rings, $is3D, $isMeasured, $srid);

        if (! $triangle->isEmpty()) {
            if ($triangle->exteriorRing()->numPoints() !== 4) {
                throw new GeometryException('A triangle must have exactly 4 points.');
            }

            if ($triangle->numInteriorRings() !== 0) {
                throw new GeometryException('A triangle cannot have interior rings.');
            }
        }

        return $triangle;
    }

    /**
     * Builds a Triangle from an array of LineString objects.
     *
     * @param LineString[] $rings
     *
     * @return Triangle
     *
     * @throws GeometryException
     */
    public static function factory(array $rings)
    {
        $triangle = parent::factory($rings);

        if (! $triangle instanceof Triangle) {
            throw new GeometryException('The LineString(s) provided do not form a Triangle');
        }

        return $triangle;
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'Triangle';
    }
}
