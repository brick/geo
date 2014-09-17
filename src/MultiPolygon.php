<?php

namespace Brick\Geo;

/**
 * A MultiPolygon is a MultiSurface object composed of Polygon elements.
 */
class MultiPolygon extends MultiSurface
{
    /**
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'MultiPolygon';
    }

    /**
     * {@inheritdoc}
     */
    public function area()
    {
        return self::getService()->area($this);
    }

    /**
     * {@inheritdoc}
     */
    public function centroid()
    {
        return self::getService()->centroid($this);
    }

    /**
     * {@inheritdoc}
     */
    public function pointOnSurface()
    {
        //@todo
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * Returns the polygons that compose this multipolygon.
     *
     * @return MultiPolygon[]
     */
    public function getPolygons()
    {
        return $this->geometries;
    }
}
