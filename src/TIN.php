<?php

namespace Brick\Geo;

use Brick\Geo\Exception\UnexpectedGeometryException;

/**
 * A TIN (triangulated irregular network) is a PolyhedralSurface consisting only of Triangle patches.
 */
class TIN extends PolyhedralSurface
{
    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedGeometryException If the patches are not triangles.
     */
    public function __construct(CoordinateSystem $cs, Polygon ...$patches)
    {
        parent::__construct($cs, ...$patches);

        foreach ($patches as $patch) {
            if (! $patch instanceof Triangle) {
                throw new UnexpectedGeometryException('The patches in a TIN must be triangles.');
            }
        }
    }

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
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryTypeBinary()
    {
        return Geometry::TIN;
    }
}
