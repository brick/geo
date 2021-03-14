<?php

declare(strict_types = 1);

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;
use Brick\Geo\GeometryCollection;
use Brick\Geo\IO\GeoJSON\Feature;
use Brick\Geo\IO\GeoJSON\FeatureCollection;
use InvalidArgumentException;
use stdClass;

/**
 * Converter class from Geometry to GeoJSON.
 */
class GeoJSONWriter
{
    private bool $prettyPrint;

    /**
     * @param bool $prettyPrint Whether to pretty-print the JSON output.
     */
    public function __construct(bool $prettyPrint = false)
    {
        $this->prettyPrint = $prettyPrint;
    }

    /**
     * @param Geometry|Feature|FeatureCollection $object The object to export as GeoJSON.
     *
     * @return string The GeoJSON representation of the given object.
     *
     * @throws GeometryIOException If the given geometry cannot be exported as GeoJSON.
     */
    public function write(object $object) : string
    {
        return json_encode($this->writeObject($object), $this->prettyPrint ? JSON_PRETTY_PRINT : 0);
    }

    /**
     * @psalm-suppress RedundantConditionGivenDocblockType
     * @psalm-suppress MixedArgument
     *
     * @param Geometry|Feature|FeatureCollection $object
     *
     * @return stdClass An object to be JSON-encoded.
     *
     * @throws GeometryIOException
     */
    private function writeObject(object $object): stdClass
    {
        if ($object instanceof Feature) {
            return $this->writeFeature($object);
        }

        if ($object instanceof FeatureCollection) {
            return $this->writeFeatureCollection($object);
        }

        if ($object instanceof Geometry) {
            return $this->writeGeometry($object);
        }

        throw new InvalidArgumentException('Expected Feature, FeatureCollection or Geometry, got ' . get_class($object));
    }

    /**
     * @throws GeometryIOException
     */
    private function writeFeature(Feature $feature): stdClass
    {
        $geometry = $feature->getGeometry();

        if ($geometry !== null) {
            $geometry = $this->writeGeometry($geometry);
        }

        return (object) [
            'type' => 'Feature',
            'properties' => $feature->getProperties(),
            'geometry' => $geometry
        ];
    }

    /**
     * @throws GeometryIOException
     */
    private function writeFeatureCollection(FeatureCollection $featureCollection): stdClass
    {
        $features = $featureCollection->getFeatures();
        $features = array_map(fn(Feature $feature) => $this->writeFeature($feature), $features);

        return (object) [
            'type' => 'FeatureCollection',
            'features' => $features
        ];
    }

    /**
     * @throws GeometryIOException
     */
    private function writeGeometry(Geometry $geometry): stdClass
    {
        // filter out MultiPoint, MultiLineString and MultiPolygon
        if ($geometry instanceof GeometryCollection && $geometry->geometryType() === 'GeometryCollection') {
            return $this->writeGeometryCollection($geometry);
        }

        $geometryType = $geometry->geometryType();

        $validGeometries = [
            'Point',
            'LineString',
            'Polygon',
            'MultiPoint',
            'MultiLineString',
            'MultiPolygon'
        ];

        if (! in_array($geometryType, $validGeometries, true)) {
            throw GeometryIOException::unsupportedGeometryType($geometry->geometryType());
        }

        return (object) [
            'type' => $geometryType,
            'coordinates' => $geometry->toArray()
        ];
    }

    /**
     * @throws GeometryIOException
     */
    private function writeGeometryCollection(GeometryCollection $geometryCollection): stdClass
    {
        $geometries = $geometryCollection->geometries();

        $geometries = array_map(function(Geometry $geometry) {
            if ($geometry instanceof GeometryCollection) {
                throw new GeometryIOException('GeoJSON does not allow nested GeometryCollections.');
            }

            return $this->writeGeometry($geometry);
        }, $geometries);

        return (object) [
            'type' => 'GeometryCollection',
            'geometries' => $geometries
        ];
    }
}
