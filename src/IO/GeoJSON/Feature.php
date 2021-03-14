<?php

declare(strict_types=1);

namespace Brick\Geo\IO\GeoJSON;

use Brick\Geo\Geometry;
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
        $this->properties = $properties;
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
        $that = clone $this;
        $that->geometry = $geometry;

        return $that;
    }

    public function getProperties(): ?stdClass
    {
        return $this->properties;
    }

    /**
     * Returns a copy of this Feature with the given properties.
     *
     * @param stdClass|null $properties An optional key-value map of feature properties. Must be convertible to JSON.
     */
    public function withProperties(?stdClass $properties): Feature
    {
        $that = clone $this;
        $this->properties = $properties;

        return $that;
    }

    /**
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

        return $this->properties->{$name};
    }

    /**
     * Returns a copy of this Feature with the given property set.
     *
     * @param string $name  The property name.
     * @param mixed  $value The value. Must be convertible to JSON.
     */
    public function withProperty(string $name, $value): Feature
    {
        $that = clone $this;

        if ($that->properties === null) {
            $that->properties = new stdClass();
        }

        $that->properties->{$name} = $value;

        return $that;
    }
}
