<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Projector\Projector;

/**
 * A MultiLineString is a MultiCurve whose elements are LineStrings.
 *
 * @extends MultiCurve<LineString>
 */
class MultiLineString extends MultiCurve
{
    /**
     * @noproxy
     */
    public function geometryType() : string
    {
        return 'MultiLineString';
    }

    /**
     * @noproxy
     */
    public function geometryTypeBinary() : int
    {
        return Geometry::MULTILINESTRING;
    }

    public function dimension() : int
    {
        return 1;
    }

    protected function containedGeometryType() : string
    {
        return LineString::class;
    }

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
