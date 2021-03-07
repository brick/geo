<?php

declare(strict_types = 1);

namespace Brick\Geo\IO;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Exception\GeometryException;
use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;
use Brick\Geo\GeometryCollection;
use Brick\Geo\LineString;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiPolygon;
use Brick\Geo\Point;
use Brick\Geo\Polygon;

/**
 * Builds geometries out of GeoJSON text strings.
 */
class GeoJSONReader
{
    /**
     * The GeoJSON types, in their correct case according to the standard, indexed by their lowercase counterpart.
     */
    private const TYPES = [
        'feature'           => 'Feature',
        'featurecollection' => 'FeatureCollection',
        'point'             => 'Point',
        'multipoint'        => 'MultiPoint',
        'linestring'        => 'LineString',
        'multilinestring'   => 'MultiLineString',
        'polygon'           => 'Polygon',
        'multipolygon'      => 'MultiPolygon',
    ];

    private bool $lenient;

    /**
     * @param bool $lenient Whether to allow different cases for GeoJSON types, such as POINT instead of Point.
     *                      The standard enforces a case-sensitive comparison, so this reader is case-sensitive by
     *                      default, but you can override this behaviour here.
     */
    public function __construct(bool $lenient = false)
    {
        $this->lenient = $lenient;
    }

    /**
     * @throws GeometryException If the GeoJSON file is invalid.
     */
    public function read(string $geojson) : Geometry
    {
        $geojsonArray = json_decode($geojson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new GeometryIOException(json_last_error_msg(), json_last_error());
        }

        if (! is_array($geojsonArray)) {
            throw GeometryIOException::invalidGeoJSON('Unable to parse GeoJSON string.');
        }

        $geometry = $this->readGeoJSON($geojsonArray);

        return $geometry;
    }

    /**
     * @throws GeometryException If the GeoJSON file is invalid.
     */
    private function readGeoJSON(array $geojson) : Geometry
    {
        if (! isset($geojson['type']) || ! is_string($geojson['type'])) {
            throw GeometryIOException::invalidGeoJSON('Missing or malformed "type" attribute.');
        }

        $geoType = $this->normalizeGeoJSONType($geojson['type']);

        switch ($geoType) {
            case 'Feature':
                return $this->readFeature($geojson);

            case 'FeatureCollection':
                if (! isset($geojson['features']) || ! is_array($geojson['features'])) {
                    throw GeometryIOException::invalidGeoJSON('Missing or malformed "FeatureCollection.features" attribute.');
                }

                $geometries = [];

                foreach ($geojson['features'] as $feature) {
                    if (! is_array($feature)) {
                        throw GeometryIOException::invalidGeoJSON('Missing or malformed "FeatureCollection.features" attribute.');
                    }

                    $geometries[] = $this->readFeature($feature);
                }

                return GeometryCollection::of(...$geometries);

            case 'Point':
            case 'MultiPoint':
            case 'LineString':
            case 'MultiLineString':
            case 'Polygon':
            case 'MultiPolygon':
                return $this->readGeometry($geojson);

            default:
                throw GeometryIOException::unsupportedGeoJSONType($geojson['type']);
        }
    }

    /**
     * @throws GeometryException If the GeoJSON file is invalid.
     */
    private function readFeature(array $feature) : Geometry
    {
        // Verify type 'Feature'
        if (! isset($feature['type']) || ! is_string($feature['type']) || 'Feature' !== $this->normalizeGeoJSONType($feature['type'])) {
            throw GeometryIOException::invalidGeoJSON('Missing or malformed "Feature.type" attribute.');
        }

        // Verify geometry exists and is array
        if (! isset($feature['geometry']) || ! is_array($feature['geometry'])) {
            throw GeometryIOException::invalidGeoJSON('Missing or malformed "Feature.geometry" attribute.');
        }

        return $this->readGeometry($feature['geometry']);
    }

    /**
     * @throws GeometryException If the GeoJSON file is invalid.
     */
    private function readGeometry(array $geometry) : Geometry
    {
        // Verify geometry `type`
        if (! isset($geometry['type']) || ! is_string($geometry['type'])) {
            throw GeometryIOException::invalidGeoJSON('Missing or Malformed "Geometry.type" attribute.');
        }

        $geoType = $this->normalizeGeoJSONType($geometry['type']);

        // Verify geometry `coordinates`
        if (! isset($geometry['coordinates']) || ! array($geometry['coordinates'])) {
            throw GeometryIOException::invalidGeoJSON('Missing or malformed "Geometry.coordinates" attribute.');
        }

        /*
         * Note: we should actually check the contents of the coords array here!
         * Type-hints make static analysis happy, but errors will appear at runtime if the GeoJSON is invalid.
         */

        /** @var array $geoCoords */
        $geoCoords = $geometry['coordinates'];

        $hasZ = $this->hasZ($geoCoords);
        $hasM = false;
        $srid = 4326;

        $cs = new CoordinateSystem($hasZ, $hasM, $srid);

        switch ($geoType) {
            case 'Point':
                /** @var list<float> $geoCoords */
                return $this->genPoint($cs, $geoCoords);

            case 'MultiPoint':
                /** @var list<list<float>> $geoCoords */
                return $this->genMultiPoint($cs, $geoCoords);

            case 'LineString':
                /** @var list<list<float>> $geoCoords */
                return $this->genLineString($cs, $geoCoords);

            case 'MultiLineString':
                /** @var list<list<list<float>>> $geoCoords */
                return $this->genMultiLineString($cs, $geoCoords);

            case 'Polygon':
                /** @var list<list<list<float>>> $geoCoords */
                return $this->genPolygon($cs, $geoCoords);

            case 'MultiPolygon':
                /** @var list<list<list<list<float>>>> $geoCoords */
                return $this->genMultiPolygon($cs, $geoCoords);
        }

        throw GeometryIOException::unsupportedGeoJSONType($geoType);
    }

    /**
     * [x, y]
     *
     * @psalm-param list<float> $coords
     *
     * @param float[] $coords
     *
     * @throws GeometryException If the GeoJSON file is invalid.
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
     * @throws GeometryException If the GeoJSON file is invalid.
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
     * @throws GeometryException If the GeoJSON file is invalid.
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
     * @throws GeometryException If the GeoJSON file is invalid.
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
     * @throws GeometryException If the GeoJSON file is invalid.
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
     * @throws GeometryException If the GeoJSON file is invalid.
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
