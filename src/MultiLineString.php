<?php

declare(strict_types=1);

namespace Brick\Geo;

use Override;

/**
 * A MultiLineString is a MultiCurve whose elements are LineStrings.
 *
 * @extends MultiCurve<LineString>
 */
final readonly class MultiLineString extends MultiCurve
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

    #[Override]
    public function geometryType() : string
    {
        return 'MultiLineString';
    }

    #[Override]
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
}
