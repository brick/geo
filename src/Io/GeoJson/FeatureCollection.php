<?php

declare(strict_types=1);

namespace Brick\Geo\Io\GeoJson;

/**
 * A GeoJSON FeatureCollection. This class is immutable.
 */
final readonly class FeatureCollection
{
    /**
     * The contained features.
     *
     * @var list<Feature>
     */
    private array $features;

    public function __construct(Feature ...$features)
    {
        $this->features = array_values($features);
    }

    /**
     * @return list<Feature>
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
        return new FeatureCollection(...$this->features, ...[$feature]);
    }
}
