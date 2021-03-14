<?php

declare(strict_types=1);

namespace Brick\Geo\IO\GeoJSON;

use InvalidArgumentException;

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
    private array $features = [];

    /**
     * @psalm-suppress DocblockTypeContradiction
     *
     * @param Feature[] $features The GeoJSON Features.
     */
    public function __construct(array $features)
    {
        foreach ($features as $feature) {
            if (! $feature instanceof Feature) {
                throw new InvalidArgumentException(sprintf(
                    'Expected instance of %s, got %s.',
                    Feature::class,
                    is_object($feature) ? get_class($feature) : gettype($feature)
                ));
            }
        }

        $this->features = array_values($features);
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
