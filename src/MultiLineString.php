<?php

namespace Brick\Geo;

/**
 * A MultiLineString is a MultiCurve geometry collection composed of LineString elements.
 */
class MultiLineString extends MultiCurve
{
    /**
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'MultiLineString';
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed()
    {
        foreach ($this->geometries as $lineString) {
            /** @var LineString $lineString */
            if (! $lineString->isClosed()) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function length()
    {
        $length = 0;

        foreach ($this->geometries as $lineString) {
            /** @var LineString $lineString */
            $length += $lineString->length();
        }

        return $length;
    }
}
