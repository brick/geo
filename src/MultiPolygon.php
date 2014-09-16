<?php

namespace Brick\Geo;

/**
 * A MultiPolygon is a MultiSurface object composed of Polygon elements.
 */
class MultiPolygon extends MultiSurface
{
    /**
     * Builds a MultiPolygon from an array of Polygon objects.
     *
     * @param Polygon[] $polygons
     *
     * @return MultiPolygon
     *
     * @throws GeometryException
     */
    public static function factory(array $polygons = [])
    {
        foreach ($polygons as $polygon) {
            if (! $polygon instanceof Polygon) {
                throw new GeometryException('A MultiPolygon can only contain Polygon objects');
            }
        }

        return new MultiPolygon($polygons);
    }

    /**
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'MultiPolygon';
    }

    /**
     * @param string $wkt
     *
     * @return MultiPolygon
     */
    public static function fromText($wkt)
    {
        return parent::fromText($wkt);
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
