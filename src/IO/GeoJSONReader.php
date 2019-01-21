<?php

declare(strict_types = 1);

namespace Brick\Geo\IO;

use Brick\Geo\CoordinateSystem;
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
     * @param int    $srid    The optional SRID of the geometry.
     *
     * @return GeometryCollection|Geometry
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\GeometryIOException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    public function read(string $geojson, int $srid = 0) : Geometry
    {
        $geojsonArray = json_decode(strtoupper($geojson), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new GeometryIOException(json_last_error_msg(), json_last_error());
        }

        if (! is_array($geojsonArray)) {
            throw GeometryIOException::invalidGeoJSON('Unable to parse GeoJSON String.');
        }

        $geometry = $this->readGeoJSON($geojsonArray, $srid);

        return $geometry;
    }

    /**
     * @param array $geojson
     * @param int   $srid
     *
     * @return Geometry
     *
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     * @throws GeometryIOException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     */
    protected function readGeoJSON(array $geojson, int $srid) : Geometry
    {
        if (! isset($geojson['TYPE']) || ! is_string($geojson['TYPE'])) {
            throw GeometryIOException::invalidGeoJSON('Missing or Malformed "Type" attribute.');
        }

        switch ($geojson['TYPE']) {
            case 'FEATURE':
                return $this->readFeature($geojson, $srid);

            case 'FEATURECOLLECTION':
                // Verify 'FEATURES' exists
                if (! isset($geojson['FEATURES']) || ! is_array($geojson['FEATURES'])) {
                    throw GeometryIOException::invalidGeoJSON('Missing or Malformed "FeatureCollection.Features" attribute.');
                }

                $geometries = [];

                foreach ($geojson['FEATURES'] as $feature) {
                    $geometries[] = $this->readFeature($feature, $srid);
                }

                return GeometryCollection::of(...$geometries);

            case 'POINT':
            case 'MULTIPOINT':
            case 'LINESTRING':
            case 'MULTILINESTRING':
            case 'POLYGON':
            case 'MULTIPOLYGON':

                return $this->readGeometry($geojson, $srid);

            default:
                throw GeometryIOException::unsupportedGeoJSONType($geojson['TYPE']);
        }
    }

    /**
     * @param array $feature
     * @param int   $srid
     *
     * @return Geometry
     *
     * @throws GeometryIOException
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    protected function readFeature(array $feature, int $srid) : Geometry
    {
        // Verify Type 'FEATURE'
        if (! array_key_exists('TYPE', $feature) || 'FEATURE' !== $feature['TYPE']) {
            throw GeometryIOException::invalidGeoJSON('Missing or Malformed "Feature.Type" attribute.');
        }

        // Verify Geometry exists and is array
        if (! array_key_exists('GEOMETRY', $feature) || ! is_array($feature['GEOMETRY'])) {
            throw GeometryIOException::invalidGeoJSON('Missing "Feature.Geometry" attribute.');
        }

        return $this->readGeometry($feature['GEOMETRY'], $srid);
    }

    /**
     * @param array $geometry
     * @param int   $srid
     *
     * @return Geometry
     *
     * @throws GeometryIOException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    protected function readGeometry(array $geometry, int $srid) : Geometry
    {
        // Verify Geometry TYPE
        if (! array_key_exists('TYPE', $geometry) || ! is_string($geometry['TYPE'])) {
            throw GeometryIOException::invalidGeoJSON('Missing "Geometry.Type" attribute.');
        }

        $geoType = $geometry['TYPE'];

        // Verify Geometry COORDINATES
        if (! array_key_exists('COORDINATES', $geometry) || ! array($geometry['COORDINATES'])) {
            throw GeometryIOException::invalidGeoJSON('Missing "Geometry.Coordinates" attribute.');
        }

        $geoCoords = $geometry['COORDINATES'];

        $hasZ = $this->hasZ($geoCoords);
        $hasM = false;
        $isEmpty = empty($geoCoords);

        $cs = new CoordinateSystem($hasZ, $hasM, $srid);

        switch ($geoType) {
            case 'POINT':
                if ($isEmpty) {
                    return new Point($cs);
                }

                return $this->genPoint($cs, ...$geoCoords);

            case 'MULTIPOINT':
                if ($isEmpty) {
                    return new MultiPoint($cs);
                }

                return $this->genMultiPoint($cs, ...$geoCoords);

            case 'LINESTRING':
                if ($isEmpty) {
                    return new LineString($cs);
                }

                return $this->genLineString($cs, ...$geoCoords);

            case 'MULTILINESTRING':
                if ($isEmpty) {
                    return new MultiLineString($cs);
                }

                return $this->genMultiLineString($cs, ...$geoCoords);

            case 'POLYGON':
                if ($isEmpty) {
                    return new Polygon($cs);
                }

                return $this->genPolygon($cs, ...$geoCoords);

            case 'MULTIPOLYGON':
                if ($isEmpty) {
                    return new MultiPolygon($cs);
                }

                return $this->genMultiPolygon($cs, ...$geoCoords);
        }

        throw GeometryIOException::unsupportedGeoJSONType($geoType);
    }

    /**
     * [x, y]
     *s
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
     * @param array            $coords
     *
     * @return MultiPoint
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    private function genMultiPoint(CoordinateSystem $cs, ...$coords) : MultiPoint
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
     * @param array            $coords
     *
     * @return LineString
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     */
    private function genLineString(CoordinateSystem $cs, ...$coords) : LineString
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
     * @param array            $coords
     *
     * @return MultiLineString
     *
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    private function genMultiLineString(CoordinateSystem $cs, ...$coords) : MultiLineString
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
     * @param array            $coords
     *
     * @return Polygon
     *
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     */
    private function genPolygon(CoordinateSystem $cs, ...$coords) : Polygon
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
     * @param array            $coords
     *
     * @return MultiPolygon
     *
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     * @throws \Brick\Geo\Exception\UnexpectedGeometryException
     */
    private function genMultiPolygon(CoordinateSystem $cs, ...$coords) : MultiPolygon
    {
        $polygons = [];

        foreach ($coords as $polygonCoords) {

            $polygons[] = $this->genPolygon($cs, ...$polygonCoords);
        }

        return new MultiPolygon($cs, ...$polygons);
    }

    /**
     * @param $coords
     * @return bool
     */
    private function hasZ(array $coords)
    {
        if (empty($coords)) {
            return false;
        }

        // At least one Geometry hasZ
        if (! is_array($coords[0])) {
            if (3 === count($coords)) {
                return true;
            }
            return false;
        }

        foreach ($coords as $coord) {
            if ($this->hasZ($coord)) {
                return true;
            }
        }

        return false;
    }
}
