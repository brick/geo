<?php

namespace Brick\Geo;

/**
 * A TIN (triangulated irregular network) is a PolyhedralSurface consisting only of Triangle patches.
 */
class TIN extends PolyhedralSurface
{
    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'TIN';
    }

    /**
     * {@inheritdoc}
     */
    protected static function containedGeometryType()
    {
        return Triangle::class;
    }
}
