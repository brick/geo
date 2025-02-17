<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Exception\UnexpectedGeometryException;
use Brick\Geo\Projector\Projector;
use Override;

/**
 * A TIN (triangulated irregular network) is a PolyhedralSurface consisting only of Triangle patches.
 */
final class TIN extends PolyhedralSurface
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
