<?php

declare(strict_types=1);

namespace Brick\Geo;

/**
 * A MultiLineString is a MultiCurve whose elements are LineStrings.
 */
class MultiLineString extends MultiCurve
{
    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType() : string
    {
        return 'MultiLineString';
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryTypeBinary() : int
    {
        return Geometry::MULTILINESTRING;
    }

    /**
     * {@inheritdoc}
     */
    public function dimension() : int
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    protected function containedGeometryType() : string
    {
        return LineString::class;
    }
}
