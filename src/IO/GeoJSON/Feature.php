<?php

declare(strict_types=1);

namespace Brick\Geo\IO\GeoJSON;

use Brick\Geo\Geometry;
use Brick\Geo\Internal\Cloner;
use stdClass;

/**
 * A GeoJSON Feature. This class is immutable.
 */
final class Feature
{
    /**
     * The contained geometry, or null if this feature is not associated with a geometry.
     *
     * @var Geometry|null
     */
    private ?Geometry $geometry;

    /**
     * An optional key-value map of feature properties. Must be convertible to JSON.
     */
    private ?stdClass $properties;

    /**
     * @param Geometry|null $geometry
     * @param stdClass|null $properties
     */
    public function __construct(?Geometry $geometry = null, ?stdClass $properties = null)
    {
        $this->geometry = $geometry;
        $this->properties = Cloner::clone($properties);
    }

    public function getGeometry(): ?Geometry
    {
        return $this->geometry;
    }

    /**
     * Returns a copy of this Feature with the given geometry.
     */
    public function withGeometry(?Geometry $geometry): Feature
    {
        return new Feature($geometry, $this->properties);
    }

    public function getProperties(): ?stdClass
    {
        return Cloner::clone($this->properties);
    }

    /**
     * Returns a copy of this Feature with the given properties.
     *
     * @param stdClass|null $properties An optional key-value map of feature properties. Must be convertible to JSON.
     */
    public function withProperties(?stdClass $properties): Feature
    {
        return new Feature($this->geometry, Cloner::clone($properties));
    }

    /**
     * @psalm-suppress MixedArgument
     *
     * @param string $name    The property name.
     * @param mixed  $default The default value if the property is not found.
     *
     * @return mixed
     */
    public function getProperty(string $name, $default = null)
    {
        if ($this->properties === null || ! property_exists($this->properties, $name)) {
            return $default;
        }

        return Cloner::clone($this->properties->{$name});
    }

    /**
     * Returns a copy of this Feature with the given property set.
     *
     * @param string $name  The property name.
     * @param mixed  $value The value. Must be convertible to JSON.
     */
    public function withProperty(string $name, $value): Feature
    {
        $properties = Cloner::clone($this->properties);

        if ($properties === null) {
            $properties = new stdClass();
        }

        $properties->{$name} = $value;

        return new Feature($this->geometry, $properties);
    }
}
