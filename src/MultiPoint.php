<?php

namespace Brick\Geo;

/**
 * A MultiPoint is a 0-dimensional GeometryCollection. The elements of a MultiPoint are restricted to Points.
 *
 * The Points are not connected or ordered in any semantically important way
 * (see the discussion at GeometryCollection). A MultiPoint is simple if no two Points in the MultiPoint are equal
 * (have identical coordinate values in X and Y).
 *
 * The boundary of a MultiPoint is the empty set.
 */
class MultiPoint extends GeometryCollection
{
    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'MultiPoint';
    }

    /**
     * {@inheritdoc}
     */
    public function dimension()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected static function containedGeometryType()
    {
        return Point::class;
    }

    /**
     * {@inheritdoc}
     *
     * A MultiPoint is simple if it has no repeated points.
     *
     * Only X and Y coordinates are compared when checking for repeated points.
     */
    public function isSimple()
    {
        $count = count($this->geometries);

        for ($i = 0; $i < $count; $i++) {
            /** @var Point $a */
            $a = $this->geometries[$i];

            for ($j = $i + 1; $j < $count; $j++) {
                /** @var Point $b */
                $b = $this->geometries[$j];

                if ($a->x() === $b->x() && $a->y() === $b->y()) {
                    return false;
                }
            }
        }

        return true;
    }
}
