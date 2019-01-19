<?php

declare(strict_types = 1);

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryIOException;

/**
 * GeoJSON parser.
 */
class GeoJSONParser
{
    /**
     * Type of Geometry
     *
     * @var null|string
     */
    protected $type = null;

    /**
     * Geometry Coordinates
     *
     * @var array
     */
    protected $coordinates = [];

    /**
     * Class constructor.
     *
     * @param string $geojson
     * @throws GeometryIOException
     */
    public function __construct(string $geojson)
    {
        $geojson_array = json_decode($geojson, true);

        // Validate 'TYPE' attribute exists
        if (empty($geojson_array['TYPE'])) {
            throw new GeometryIOException("Expected 'type' attribute");
        }

        // Set geometry type
        $this->type = $geojson_array['TYPE'];

        // Recursive Call: 'TYPE' attribute = 'FEATURE'
        if ($this->getGeometryType() == "FEATURE" && ! empty($geojson_array['GEOMETRY'])) {
            return new GeoJSONParser(json_encode($geojson_array['GEOMETRY']));
        }

        // Validate 'COORDINATES' attribute exists
        if (! is_array($geojson_array['COORDINATES'])) {
            throw new GeometryIOException("Expected 'coordinates' attribute to ba an array");
        }

        // TODO: Recursively Verify 'coordinates' attribute is of type []|numeric[]|[numeric[]]

        // Set geometry coordinates
        $this->coordinates = $geojson_array['COORDINATES'];
    }

    /**
     * @return null|string
     */
    public function getGeometryType() : ?string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getGeometryCoordinates() : array
    {
        return $this->coordinates;
    }


}