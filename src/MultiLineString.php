<?php

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
    public function geometryType()
    {
        return 'MultiLineString';
    }

    /**
     * {@inheritdoc}
     */
    public function dimension()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    protected static function containedGeometryType()
    {
        return LineString::class;
    }
}
