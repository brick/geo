<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Attribute\NoProxy;
use Brick\Geo\Projector\Projector;
use Override;

/**
 * A MultiLineString is a MultiCurve whose elements are LineStrings.
 *
 * @extends MultiCurve<LineString>
 * @final
 */
class MultiLineString extends MultiCurve
{
    /**
     * @return list<list<list<float>>>
     */
    #[Override]
    public function toArray() : array
    {
        return array_map(
            fn(LineString $lineString) => $lineString->toArray(),
            $this->geometries,
        );
    }

    #[NoProxy, Override]
    public function geometryType() : string
    {
        return 'MultiLineString';
    }

    #[NoProxy, Override]
    public function geometryTypeBinary() : int
    {
        return Geometry::MULTILINESTRING;
    }

    #[Override]
    public function dimension() : int
    {
        return 1;
    }

    #[Override]
    protected function containedGeometryType() : string
    {
        return LineString::class;
    }

    #[Override]
    public function project(Projector $projector): MultiLineString
    {
        return new MultiLineString(
            $projector->getTargetCoordinateSystem($this->coordinateSystem),
            ...array_map(
                fn (LineString $lineString) => $lineString->project($projector),
                $this->geometries,
            ),
        );
    }
}
