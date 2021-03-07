<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Exception\UnexpectedGeometryException;

/**
 * A TIN (triangulated irregular network) is a PolyhedralSurface consisting only of Triangle patches.
 */
class TIN extends PolyhedralSurface
{
    /**
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
     */
    public function geometryType() : string
    {
        return 'TIN';
    }

    /**
     * @noproxy
     */
    public function geometryTypeBinary() : int
    {
        return Geometry::TIN;
    }
}
