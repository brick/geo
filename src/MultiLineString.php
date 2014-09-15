<?php

namespace Brick\Geo;

/**
 * A MultiLineString is a MultiCurve geometry collection composed of LineString elements.
 */
class MultiLineString extends GeometryCollection
{
    /**
     * Builds a MultiLineString from an array of LineString objects
     *
     * @param  LineString[]      $lineStrings
     * @return MultiLineString
     * @throws GeometryException
     */
    public static function factory(array $lineStrings)
    {
        foreach ($lineStrings as $lineString) {
            if (! $lineString instanceof LineString) {
                throw new GeometryException('A MultiLineString can only contain LineString objects');
            }
        }

        return new MultiLineString($lineStrings);
    }

    /**
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'MultiLineString';
    }
}
