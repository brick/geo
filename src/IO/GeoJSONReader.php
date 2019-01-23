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
 * Builds geometries out of GeoJSON Text strings.
 */
class GeoJSONReader
{
    /**
     * @param string $geojson The GeoJSON to read.
     *
     * @return GeometryCollection|Geometry
     *
     * @throws GeometryException If the GeoJSON file is invalid.
     */
    public function read(string $geojson) : Geometry
    {
        $geojsonArray = json_decode($geojson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new GeometryIOException(json_last_error_msg(), json_last_error());
        }

        if (! is_array($geojsonArray)) {
            throw GeometryIOException::invalidGeoJSON('Unable to parse GeoJSON String.');
        }

        $geometry = $this->readGeoJSON($geojsonArray);

        return $geometry;
    }

    /**
     * @param array $geojson
     *
     * @return Geometry
     *
     * @throws GeometryIOException
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    protected function readGeoJSON(array $geojson) : Geometry
    {
        if (! isset($geojson['type']) || ! is_string($geojson['type'])) {
            throw GeometryIOException::invalidGeoJSON('Missing or Malformed "type" attribute.');
        }

        switch ($geojson['type']) {
            case 'Feature':
                return $this->readFeature($geojson);

            case 'FeatureCollection':
                // Verify 'FEATURES' exists
                if (! isset($geojson['features']) || ! is_array($geojson['features'])) {
                    throw GeometryIOException::invalidGeoJSON('Missing or Malformed "FeatureCollection.features" attribute.');
                }

                $geometries = [];

                foreach ($geojson['features'] as $feature) {
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
     * @param array $feature
     *
     * @return Geometry
     *
     * @throws GeometryIOException
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    protected function readFeature(array $feature) : Geometry
    {
        // Verify Type 'FEATURE'
        if (! array_key_exists('type', $feature) || 'Feature' !== $feature['type']) {
            throw GeometryIOException::invalidGeoJSON('Missing or Malformed "Feature.type" attribute.');
        }

        // Verify Geometry exists and is array
        if (! array_key_exists('geometry', $feature) || ! is_array($feature['geometry'])) {
            throw GeometryIOException::invalidGeoJSON('Missing "Feature.geometry" attribute.');
        }

        return $this->readGeometry($feature['geometry']);
    }

    /**
     * @param array $geometry
     *
     * @return Geometry
     *
     * @throws GeometryIOException
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    protected function readGeometry(array $geometry) : Geometry
    {
        // Verify Geometry TYPE
        if (! array_key_exists('type', $geometry) || ! is_string($geometry['type'])) {
            throw GeometryIOException::invalidGeoJSON('Missing "Geometry.type" attribute.');
        }

        $geoType = $geometry['type'];

        // Verify Geometry COORDINATES
        if (! array_key_exists('coordinates', $geometry) || ! array($geometry['coordinates'])) {
            throw GeometryIOException::invalidGeoJSON('Missing "Geometry.coordinates" attribute.');
        }

        $geoCoords = $geometry['coordinates'];

        $hasZ = $this->hasZ($geoCoords);
        $hasM = false;
        $srid = 4326;

        $cs = new CoordinateSystem($hasZ, $hasM, $srid);

        switch ($geoType) {
            case 'Point':
                return $this->genPoint($cs, ...$geoCoords);

            case 'MultiPoint':
                return $this->genMultiPoint($cs, ...$geoCoords);

            case 'LineString':
                return $this->genLineString($cs, ...$geoCoords);

            case 'MultiLineString':
                return $this->genMultiLineString($cs, ...$geoCoords);

            case 'Polygon':
                return $this->genPolygon($cs, ...$geoCoords);

            case 'MultiPolygon':
                return $this->genMultiPolygon($cs, ...$geoCoords);
        }

        throw GeometryIOException::unsupportedGeoJSONType($geoType);
    }

    /**
     * [x, y]
     *
     * @param CoordinateSystem $cs
     * @param float[]          ...$coords
     *
     * @return Point
     *
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     */
    private function genPoint(CoordinateSystem $cs, float ...$coords) : Point
    {
        return new Point($cs, ...$coords);
    }

    /**
     * [[x, y], ...]
     *
     * @param CoordinateSystem $cs
     * @param array[]          ...$coords
     *
     * @return MultiPoint
     *
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    private function genMultiPoint(CoordinateSystem $cs, array ...$coords) : MultiPoint
    {
        $points = [];

        foreach ($coords as $pointCoords) {
            $points[] = $this->genPoint($cs, ...$pointCoords);
        }

        return new MultiPoint($cs, ...$points);
    }

    /**
     * [[x, y], ...]
     *
     * @param CoordinateSystem $cs
     * @param array[]          ...$coords
     *
     * @return LineString
     *
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     */
    private function genLineString(CoordinateSystem $cs, array ...$coords) : LineString
    {
        $points = [];

        foreach ($coords as $pointCoords) {
            $points[] = $this->genPoint($cs, ...$pointCoords);
        }

        return new LineString($cs, ...$points);
    }

    /**
     * [[[x, y], ...], ...]
     *
     * @param CoordinateSystem $cs
     * @param array[]          ...$coords
     *
     * @return MultiLineString
     *
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    private function genMultiLineString(CoordinateSystem $cs, array ...$coords) : MultiLineString
    {
        $lineStrings = [];

        foreach ($coords as $lineStringCoords) {
            $lineStrings[] = $this->genLineString($cs, ...$lineStringCoords);
        }

        return new MultiLineString($cs, ...$lineStrings);
    }

    /**
     * [[[x, y], ...], ...]
     *
     * @param CoordinateSystem $cs
     * @param array[]          ...$coords
     *
     * @return Polygon
     *
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     */
    private function genPolygon(CoordinateSystem $cs, array ...$coords) : Polygon
    {
        $lineStrings = [];

        foreach ($coords as $lineStringCoords) {
            $lineStrings[] = $this->genLineString($cs, ...$lineStringCoords);
        }

        return new Polygon($cs, ...$lineStrings);
    }

    /**
     * [[[x, y], ...], ...]
     *
     * @param CoordinateSystem $cs
     * @param array[]          ...$coords
     *
     * @return MultiPolygon
     *
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    private function genMultiPolygon(CoordinateSystem $cs, array ...$coords) : MultiPolygon
    {
        $polygons = [];

        foreach ($coords as $polygonCoords) {
            $polygons[] = $this->genPolygon($cs, ...$polygonCoords);
        }

        return new MultiPolygon($cs, ...$polygons);
    }

    /**
     * @param $coords
     *
     * @return bool
     */
    private function hasZ(array $coords)
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
}
