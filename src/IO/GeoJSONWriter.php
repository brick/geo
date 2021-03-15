<?php

declare(strict_types = 1);

namespace Brick\Geo\IO;

use Brick\Geo\BoundingBox;
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

    private bool $setBbox;

    /**
     * @param bool $prettyPrint Whether to pretty-print the JSON output.
     * @param bool $setBbox     Whether to set the bbox attribute of each non-empty GeoJSON object.
     */
    public function __construct(bool $prettyPrint = false, bool $setBbox = false)
    {
        $this->prettyPrint = $prettyPrint;
        $this->setBbox = $setBbox;
    }

    /**
     * Writes the given object as GeoJSON.
     *
     * @param Geometry|Feature|FeatureCollection $object The object to export as GeoJSON.
     *
     * @return string The GeoJSON representation of the given object.
     *
     * @throws GeometryIOException If the given geometry cannot be exported as GeoJSON.
     */
    public function write(object $object) : string
    {
        return json_encode($this->writeRaw($object), $this->prettyPrint ? JSON_PRETTY_PRINT : 0);
    }

    /**
     * Writes the given object as a raw stdClass object that can be JSON-encoded.
     *
     * @psalm-suppress RedundantConditionGivenDocblockType
     * @psalm-suppress MixedArgument
     *
     * @param Geometry|Feature|FeatureCollection $object
     *
     * @return stdClass An object to be JSON-encoded.
     *
     * @throws GeometryIOException
     */
    public function writeRaw(object $object): stdClass
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
        $boundingBox = null;
        $geometry = $feature->getGeometry();

        if ($geometry !== null) {
            if ($this->setBbox) {
                $boundingBox = $geometry->getBoundingBox();
            }

            $geometry = $this->writeGeometry($geometry);
        }

        $result = [
            'type' => 'Feature',
            'properties' => $feature->getProperties(),
            'geometry' => $geometry
        ];

        if ($boundingBox !== null && ! $boundingBox->isEmpty()) {
            $result['bbox'] = $this->bboxToCoordinateArray($boundingBox);
        }

        return (object) $result;
    }

    /**
     * @throws GeometryIOException
     */
    private function writeFeatureCollection(FeatureCollection $featureCollection): stdClass
    {
        $features = $featureCollection->getFeatures();
        $features = array_map(fn(Feature $feature) => $this->writeFeature($feature), $features);

        $result = [
            'type' => 'FeatureCollection',
            'features' => $features
        ];

        if ($this->setBbox) {
            $boundingBox = new BoundingBox();

            foreach ($featureCollection->getFeatures() as $feature) {
                $featureGeometry = $feature->getGeometry();

                if ($featureGeometry !== null) {
                    $boundingBox = $boundingBox->extendedWithBoundingBox($featureGeometry->getBoundingBox());
                }
            }

            if (! $boundingBox->isEmpty()) {
                $result['bbox'] = $this->bboxToCoordinateArray($boundingBox);
            }
        }

        return (object) $result;
    }

    /**
     * @throws GeometryIOException
     */
    private function writeGeometry(Geometry $geometry): stdClass
    {
        // GeoJSON supports XY & XYZ only
        $geometry = $geometry->withoutM();

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

        $result = [
            'type' => $geometryType,
            'coordinates' => $geometry->toArray()
        ];

        $boundingBox = $geometry->getBoundingBox();

        if ($this->setBbox && ! $boundingBox->isEmpty()) {
            $result['bbox'] = $this->bboxToCoordinateArray($boundingBox);
        }

        return (object) $result;
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

        $result = [
            'type' => 'GeometryCollection',
            'geometries' => $geometries
        ];

        $boundingBox = $geometryCollection->getBoundingBox();

        if ($this->setBbox && ! $boundingBox->isEmpty()) {
            $result['bbox'] = $this->bboxToCoordinateArray($boundingBox);
        }

        return (object) $result;
    }

    private function bboxToCoordinateArray(BoundingBox $boundingBox): array
    {
        return array_merge(
            $boundingBox->getSouthWest()->toArray(),
            $boundingBox->getNorthEast()->toArray()
        );
    }
}
