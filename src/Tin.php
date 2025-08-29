<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Attribute\NoProxy;
use Brick\Geo\Projector\Projector;
use Override;

use function array_map;

/**
 * A TIN (triangulated irregular network) is a PolyhedralSurface consisting only of Triangle patches.
 *
 * @template-extends PolyhedralSurface<Triangle>
 *
 * @final
 */
class Tin extends PolyhedralSurface
{
    #[NoProxy, Override]
    public function geometryType(): string
    {
        return 'TIN';
    }

    #[NoProxy, Override]
    public function geometryTypeBinary(): int
    {
        return Geometry::TIN;
    }

    #[Override]
    public function project(Projector $projector): Tin
    {
        return new Tin(
            $projector->getTargetCoordinateSystem($this->coordinateSystem),
            ...array_map(
                fn (Polygon $patch) => $patch->project($projector),
                $this->patches,
            ),
        );
    }

    #[Override]
    protected function patchType(): string
    {
        return Triangle::class;
    }
}
