<?php

namespace Brick\Geo;

/**
 * A MultiLineString is a MultiCurve whose elements are LineStrings.
 */
class MultiLineString extends MultiCurve
{
    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'MultiLineString';
    }

    /**
     * {@inheritdoc}
     */
    public function dimension()
    {
        return 1;
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

    /**
     * {@inheritdoc}
     */
    protected static function containedGeometryType()
    {
        return LineString::class;
    }
}
