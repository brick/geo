<?php

declare(strict_types = 1);

namespace Brick\Geo\Io;

use Brick\Geo\BoundingBox;
use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\Geometry;
use Brick\Geo\GeometryCollection;
use Brick\Geo\Io\GeoJson\Feature;
use Brick\Geo\Io\GeoJson\FeatureCollection;
use stdClass;

/**
 * Converter class from Geometry to GeoJSON.
 */
final readonly class GeoJsonWriter
{
    private bool $prettyPrint;

    private bool $setBbox;

    private bool $lenient;

    /**
     * @param bool $prettyPrint Whether to pretty-print the JSON output.
     * @param bool $setBbox     Whether to set the bbox attribute of each non-empty GeoJSON object.
     * @param bool $lenient     Whether to allow nested GeometryCollections, forbidden by the GeoJSON spec.
     */
    public function __construct(bool $prettyPrint = false, bool $setBbox = false, bool $lenient = false)
    {
        $this->prettyPrint = $prettyPrint;
        $this->setBbox = $setBbox;
        $this->lenient = $lenient;
    }

    /**
     * Writes the given object as GeoJSON.
     *
     * @param Geometry|Feature|FeatureCollection $object The object to export as GeoJSON.
     *
     * @return string The GeoJSON representation of the given object.
     *
     * @throws GeometryIoException If the given geometry cannot be exported as GeoJSON.
     */
    public function write(Geometry|Feature|FeatureCollection $object) : string
    {
        $flags = JSON_THROW_ON_ERROR;

        if ($this->prettyPrint) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($this->writeRaw($object), $flags);
    }

    /**
     * Writes the given object as a raw stdClass object that can be JSON-encoded.
     *
     * @param Geometry|Feature|FeatureCollection $object
     *
     * @return stdClass An object to be JSON-encoded.
     *
     * @throws GeometryIoException
     */
    public function writeRaw(Geometry|Feature|FeatureCollection $object): stdClass
    {
        if ($object instanceof Feature) {
            return $this->writeFeature($object);
        }

        if ($object instanceof FeatureCollection) {
            return $this->writeFeatureCollection($object);
        }

        return $this->writeGeometry($object);
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     * @see https://github.com/vimeo/psalm/issues/8187
     *
     * @throws GeometryIoException
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
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     * @see https://github.com/vimeo/psalm/issues/8187
     *
     * @throws GeometryIoException
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
            $boundingBox = BoundingBox::new();

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
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     * @see https://github.com/vimeo/psalm/issues/8187
     *
     * @throws GeometryIoException
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
            throw GeometryIoException::unsupportedGeometryType($geometry->geometryType());
        }

        $result = [
            'type' => $geometryType,
            'coordinates' => $geometry->toArray()
        ];

        if ($this->setBbox) {
            $boundingBox = $geometry->getBoundingBox();

            if (! $boundingBox->isEmpty()) {
                $result['bbox'] = $this->bboxToCoordinateArray($boundingBox);
            }
        }

        return (object) $result;
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     * @see https://github.com/vimeo/psalm/issues/8187
     *
     * @throws GeometryIoException
     */
    private function writeGeometryCollection(GeometryCollection $geometryCollection): stdClass
    {
        $geometries = $geometryCollection->geometries();

        $geometries = array_map(function(Geometry $geometry) {
            if ($geometry::class === GeometryCollection::class && ! $this->lenient) {
                throw new GeometryIoException(
                    'GeoJSON does not allow nested GeometryCollections. ' .
                    'You can allow this by setting the $lenient flag to true.',
                );
            }

            return $this->writeGeometry($geometry);
        }, $geometries);

        $result = [
            'type' => 'GeometryCollection',
            'geometries' => $geometries
        ];

        if ($this->setBbox) {
            $boundingBox = $geometryCollection->getBoundingBox();

            if (! $boundingBox->isEmpty()) {
                $result['bbox'] = $this->bboxToCoordinateArray($boundingBox);
            }
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
