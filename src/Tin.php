<?php

declare(strict_types=1);

namespace Brick\Geo;

use Override;

/**
 * A TIN (triangulated irregular network) is a PolyhedralSurface consisting only of Triangle patches.
 *
 * @template-extends PolyhedralSurface<Triangle>
 */
final readonly class Tin extends PolyhedralSurface
{
    #[Override]
    protected function patchType() : string
    {
        return Triangle::class;
    }

    #[Override]
    public function geometryType() : string
    {
        return 'TIN';
    }

    #[Override]
    public function geometryTypeBinary() : int
    {
        return Geometry::TIN;
    }
}
