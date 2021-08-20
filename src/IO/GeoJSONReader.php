<?php

declare(strict_types = 1);

namespace Brick\Geo\IO;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Exception\GeometryException;
use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;
use Brick\Geo\GeometryCollection;
use Brick\Geo\IO\GeoJSON\Feature;
use Brick\Geo\IO\GeoJSON\FeatureCollection;
use Brick\Geo\LineString;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiPolygon;
use Brick\Geo\Point;
use Brick\Geo\Polygon;
use JsonException;
use stdClass;

/**
 * Builds geometries out of GeoJSON text strings.
 */
class GeoJSONReader
{
    /**
     * The GeoJSON types, in their correct case according to the standard, indexed by their lowercase counterpart.
     */
    private const TYPES = [
        'feature'            => 'Feature',
        'featurecollection'  => 'FeatureCollection',
        'point'              => 'Point',
        'linestring'         => 'LineString',
        'polygon'            => 'Polygon',
        'multipoint'         => 'MultiPoint',
        'multilinestring'    => 'MultiLineString',
        'multipolygon'       => 'MultiPolygon',
        'geometrycollection' => 'GeometryCollection',
    ];

    private bool $lenient;

    /**
     * @param bool $lenient Whether to parse the GeoJSON in lenient mode. This mode allows different cases for GeoJSON
     *                      types, such as POINT instead of Point, even though the standard enforces a case-sensitive
     *                      comparison. This mode also allows nested GeometryCollections, and missing Feature props.
     */
    public function __construct(bool $lenient = false)
    {
        $this->lenient = $lenient;
    }

    /**
     * @return Geometry|Feature|FeatureCollection
     *
     * @throws GeometryException If the GeoJSON file is invalid.
     */
    public function read(string $geoJson): object
    {
        try {
            $geoJsonObject = json_decode($geoJson, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw GeometryIOException::invalidGeoJSON('Unable to parse GeoJSON string.', $e);
        }

        if (! is_object($geoJsonObject)) {
            throw GeometryIOException::invalidGeoJSON('GeoJSON string does not represent an object.');
        }

        /** @var stdClass $geoJsonObject */
        return $this->readAsObject($geoJsonObject);
    }

    /**
     * @return Geometry|Feature|FeatureCollection
     *
     * @throws GeometryException
     */
    private function readAsObject(stdClass $geoJsonObject): object
    {
        if (! isset($geoJsonObject->type) || ! is_string($geoJsonObject->type)) {
            throw GeometryIOException::invalidGeoJSON('Missing or malformed "type" attribute.');
        }

        $geoType = $this->normalizeGeoJSONType($geoJsonObject->type);

        switch ($geoType) {
            case 'Feature':
                return $this->readFeature($geoJsonObject);

            case 'FeatureCollection':
                return $this->readFeatureCollection($geoJsonObject);

            case 'Point':
            case 'LineString':
            case 'Polygon':
            case 'MultiPoint':
            case 'MultiLineString':
            case 'MultiPolygon':
            case 'GeometryCollection':
                return $this->readGeometry($geoJsonObject);

            default:
                throw GeometryIOException::unsupportedGeoJSONType($geoJsonObject->type);
        }
    }

    /**
     * @psalm-suppress TypeDoesNotContainType
     *
     * @throws GeometryException
     */
    private function readFeature(stdClass $geoJsonFeature) : Feature
    {
        $this->verifyType($geoJsonFeature, 'Feature');

        $geometry = null;

        if (property_exists($geoJsonFeature, 'geometry')) {
            if ($geoJsonFeature->geometry !== null) {
                if (! is_object($geoJsonFeature->geometry)) {
                    throw GeometryIOException::invalidGeoJSON('Malformed "Feature.geometry" attribute.');
                }

                /** @var stdClass $geoJsonFeature->geometry */
                $geometry = $this->readGeometry($geoJsonFeature->geometry);
            }
        } elseif (! $this->lenient) {
            throw GeometryIOException::invalidGeoJSON('Missing "Feature.geometry" attribute.');
        }

        $properties = null;

        if (property_exists($geoJsonFeature, 'properties')) {
            if ($geoJsonFeature->properties !== null) {
                if (! is_object($geoJsonFeature->properties)) {
                    throw GeometryIOException::invalidGeoJSON('Malformed "Feature.properties" attribute.');
                }

                /** @var stdClass $properties */
                $properties = $geoJsonFeature->properties;
            }
        } elseif (! $this->lenient) {
            throw GeometryIOException::invalidGeoJSON('Missing "Feature.properties" attribute.');
        }

        return new Feature($geometry, $properties);
    }

    /**
     * @throws GeometryException
     */
    private function readFeatureCollection(stdClass $geoJsonFeatureCollection) : FeatureCollection
    {
        $this->verifyType($geoJsonFeatureCollection, 'FeatureCollection');

        if (! property_exists($geoJsonFeatureCollection, 'features')) {
            throw GeometryIOException::invalidGeoJSON('Missing "FeatureCollection.features" attribute.');
        }

        if (! is_array($geoJsonFeatureCollection->features)) {
            throw GeometryIOException::invalidGeoJSON('Malformed "FeatureCollection.features" attribute.');
        }

        $features = [];

        foreach ($geoJsonFeatureCollection->features as $feature) {
            if (! is_object($feature)) {
                throw GeometryIOException::invalidGeoJSON(sprintf(
                    'Unexpected data of type %s in "FeatureCollection.features" attribute.',
                    gettype($features)
                ));
            }

            /** @var stdClass $feature */
            $features[] = $this->readFeature($feature);
        }

        return new FeatureCollection(...$features);
    }

    /**
     * @throws GeometryException
     */
    private function readGeometry(stdClass $geoJsonGeometry) : Geometry
    {
        if (! isset($geoJsonGeometry->type) || ! is_string($geoJsonGeometry->type)) {
            throw GeometryIOException::invalidGeoJSON('Missing or malformed "Geometry.type" attribute.');
        }

        $geoType = $this->normalizeGeoJSONType($geoJsonGeometry->type);

        if ($geoType === 'GeometryCollection') {
            return $this->readGeometryCollection($geoJsonGeometry);
        }

        // Verify geometry `coordinates`
        if (! isset($geoJsonGeometry->coordinates) || ! is_array($geoJsonGeometry->coordinates)) {
            throw GeometryIOException::invalidGeoJSON(sprintf('Missing or malformed "%s.coordinates" attribute.', $geoType));
        }

        /*
         * Note: we should actually check the contents of the coords array here!
         * Type-hints make static analysis happy, but errors will appear at runtime if the GeoJSON is invalid.
         */

        $coordinates = $geoJsonGeometry->coordinates;

        $hasZ = $this->hasZ($coordinates);
        $hasM = false;
        $srid = 4326;

        $cs = new CoordinateSystem($hasZ, $hasM, $srid);

        switch ($geoType) {
            case 'Point':
                /** @var list<float> $coordinates */
                return $this->genPoint($cs, $coordinates);

            case 'LineString':
                /** @var list<list<float>> $coordinates */
                return $this->genLineString($cs, $coordinates);

            case 'Polygon':
                /** @var list<list<list<float>>> $coordinates */
                return $this->genPolygon($cs, $coordinates);

            case 'MultiPoint':
                /** @var list<list<float>> $coordinates */
                return $this->genMultiPoint($cs, $coordinates);

            case 'MultiLineString':
                /** @var list<list<list<float>>> $coordinates */
                return $this->genMultiLineString($cs, $coordinates);

            case 'MultiPolygon':
                /** @var list<list<list<list<float>>>> $coordinates */
                return $this->genMultiPolygon($cs, $coordinates);
        }

        throw GeometryIOException::unsupportedGeoJSONType($geoType);
    }

    /**
     * @throws GeometryException
     */
    private function readGeometryCollection(stdClass $jsonGeometryCollection): GeometryCollection
    {
        $this->verifyType($jsonGeometryCollection, 'GeometryCollection');

        if (! isset($jsonGeometryCollection->geometries)) {
            throw GeometryIOException::invalidGeoJSON('Missing "GeometryCollection.geometries" attribute.');
        }

        if (! is_array($jsonGeometryCollection->geometries)) {
            throw GeometryIOException::invalidGeoJSON('Malformed "GeometryCollection.geometries" attribute.');
        }

        $geometries = [];

        foreach ($jsonGeometryCollection->geometries as $geometry) {
            if (! is_object($geometry)) {
                throw GeometryIOException::invalidGeoJSON(sprintf(
                    'Unexpected data of type %s in "GeometryCollection.geometries" attribute.',
                    gettype($geometry)
                ));
            }

            if (isset($geometry->type) && $geometry->type === 'GeometryCollection' && ! $this->lenient) {
                throw new GeometryIOException('GeoJSON does not allow nested GeometryCollections.');
            }

            /** @var stdClass $geometry */
            $geometries[] = $this->readGeometry($geometry);
        }

        if (! $geometries) {
            return new GeometryCollection(CoordinateSystem::xy(4326));
        }

        return GeometryCollection::of(...$geometries);
    }

    /**
     * [x, y]
     *
     * @psalm-param list<float> $coords
     *
     * @param float[] $coords
     *
     * @throws GeometryException
     */
    private function genPoint(CoordinateSystem $cs, array $coords) : Point
    {
        return new Point($cs, ...$coords);
    }

    /**
     * [[x, y], ...]
     *
     * @psalm-param list<list<float>> $coords
     *
     * @param float[][] $coords
     *
     * @throws GeometryException
     */
    private function genMultiPoint(CoordinateSystem $cs, array $coords) : MultiPoint
    {
        $points = [];

        foreach ($coords as $pointCoords) {
            $points[] = $this->genPoint($cs, $pointCoords);
        }

        return new MultiPoint($cs, ...$points);
    }

    /**
     * [[x, y], ...]
     *
     * @psalm-param list<list<float>> $coords
     *
     * @param float[][] $coords
     *
     * @throws GeometryException
     */
    private function genLineString(CoordinateSystem $cs, array $coords) : LineString
    {
        $points = [];

        foreach ($coords as $pointCoords) {
            $points[] = $this->genPoint($cs, $pointCoords);
        }

        return new LineString($cs, ...$points);
    }

    /**
     * [[[x, y], ...], ...]
     *
     * @psalm-param list<list<list<float>>> $coords
     *
     * @param float[][][] $coords
     *
     * @throws GeometryException
     */
    private function genMultiLineString(CoordinateSystem $cs, array $coords) : MultiLineString
    {
        $lineStrings = [];

        foreach ($coords as $lineStringCoords) {
            $lineStrings[] = $this->genLineString($cs, $lineStringCoords);
        }

        return new MultiLineString($cs, ...$lineStrings);
    }

    /**
     * [[[x, y], ...], ...]
     *
     * @psalm-param list<list<list<float>>> $coords
     *
     * @param float[][][] $coords
     *
     * @throws GeometryException
     */
    private function genPolygon(CoordinateSystem $cs, array $coords) : Polygon
    {
        $lineStrings = [];

        foreach ($coords as $lineStringCoords) {
            $lineStrings[] = $this->genLineString($cs, $lineStringCoords);
        }

        return new Polygon($cs, ...$lineStrings);
    }

    /**
     * [[[[x, y], ...], ...], ...]
     *
     * @psalm-param list<list<list<list<float>>>> $coords
     *
     * @param float[][][][] $coords
     *
     * @throws GeometryException
     */
    private function genMultiPolygon(CoordinateSystem $cs, array $coords) : MultiPolygon
    {
        $polygons = [];

        foreach ($coords as $polygonCoords) {
            $polygons[] = $this->genPolygon($cs, $polygonCoords);
        }

        return new MultiPolygon($cs, ...$polygons);
    }

    /**
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArgument
     *
     * @param array $coords A potentially nested list of floats.
     */
    private function hasZ(array $coords) : bool
    {
        if (empty($coords)) {
            return false;
        }

        // At least one Geometry hasZ
        if (! is_array($coords[0])) {
            return 3 === count($coords);
        }

        foreach ($coords as $coord) {
            if ($this->hasZ($coord)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws GeometryIOException
     */
    private function verifyType(stdClass $geoJsonObject, string $type): void
    {
        if (isset($geoJsonObject->type) && is_string($geoJsonObject->type)){
            if ($this->normalizeGeoJSONType($geoJsonObject->type) === $type) {
                return;
            }
        }

        throw GeometryIOException::invalidGeoJSON(sprintf('Missing or malformed "%s.type" attribute.', $type));
    }

    /**
     * Normalizes the given GeoJSON type.
     *
     * If the type is not recognized, it is returned as is.
     */
    private function normalizeGeoJSONType(string $type) : string
    {
        if ($this->lenient) {
            $type = strtolower($type);

            if (isset(self::TYPES[$type])) {
                return self::TYPES[$type];
            }
        }

        return $type;
    }
}
