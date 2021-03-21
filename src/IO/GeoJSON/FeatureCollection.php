<?php

declare(strict_types=1);

namespace Brick\Geo\IO\GeoJSON;

/**
 * A GeoJSON FeatureCollection. This class is immutable.
 */
final class FeatureCollection
{
    /**
     * The contained features.
     *
     * @var Feature[]
     */
    private array $features;

    public function __construct(Feature ...$features)
    {
        $this->features = $features;
    }

    /**
     * @return Feature[]
     */
    public function getFeatures(): array
    {
        return $this->features;
    }

    /**
     * Returns a copy of this FeatureCollection with the given Feature added.
     * This instance is immutable and unaffected by this method call.
     */
    public function withAddedFeature(Feature $feature): FeatureCollection
    {
        $that = clone $this;

        $that->features[] = $feature;

        return $that;
    }
}
