<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Exception\UnexpectedGeometryException;
use Brick\Geo\Projector\Projector;
use Override;

/**
 * A TIN (triangulated irregular network) is a PolyhedralSurface consisting only of Triangle patches.
 *
 * @template-extends PolyhedralSurface<Triangle>
 */
final class TIN extends PolyhedralSurface
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

    #[Override]
    public function project(Projector $projector): TIN
    {
        return new TIN(
            $projector->getTargetCoordinateSystem($this->coordinateSystem),
            ...array_map(
                fn (Polygon $patch) => $patch->project($projector),
                $this->patches,
            ),
        );
    }
}
