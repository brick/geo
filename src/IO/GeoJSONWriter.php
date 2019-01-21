<?php

declare(strict_types = 1);

namespace Brick\Geo\IO;

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
 * Converter class from Geometry to GeoJSON.
 */
class GeoJSONWriter
{
    /**
     * @param Geometry $geometry The geometry to export as GeoJSON.
     *
     * @return string The GeoJSON representation of the given geometry.
     *
     * @throws GeometryIOException If the given geometry cannot be exported as GeoJSON.
     */
    public function write(Geometry $geometry) : string
    {
        if ($geometry instanceof GeometryCollection) {
            return $this->writeFeatureCollection($geometry);
        }

        return $this->genGeoJSONString($this->formatGeoJSONGeometry($geometry));
    }

    /**
     * @param Geometry $geometry
     *
     * @return array
     *
     * @throws GeometryIOException
     */
    private function formatGeoJSONGeometry(Geometry $geometry) : array
    {
        if ($geometry instanceof Point) {
            $type = 'Point';
        } elseif ($geometry instanceof MultiPoint) {
            $type = 'MultiPoint';
        } elseif ($geometry instanceof LineString) {
            $type = 'LineString';
        } elseif ($geometry instanceof MultiLineString) {
            $type = 'MultiLineString';
        } elseif ($geometry instanceof Polygon) {
            $type = 'Polygon';
        } elseif ($geometry instanceof MultiPolygon) {
            $type = 'MultiPolygon';
        } else {
            throw GeometryIOException::unsupportedGeometryType($geometry->geometryType());
        }

        return [
            'type' => $type,
            'coordinates' => $geometry->toArray()
        ];
    }

    /**
     * @param GeometryCollection $geometryCollection
     *
     * @return string
     * @throws GeometryIOException
     */
    private function writeFeatureCollection(GeometryCollection $geometryCollection) : string
    {
        $geojsonArray = [
            'type' => 'FeatureCollection',
            'features' => []
        ];

        if ($geometryCollection->isEmpty()) {
            return $this->genGeoJSONString($geojsonArray);
        }

        foreach ($geometryCollection->geometries() as $geometry) {
            $geojsonArray['features'][] = [
                'type' => 'Feature',
                'geometry' => $this->formatGeoJSONGeometry($geometry)
            ];
        }

        return $this->genGeoJSONString($geojsonArray);
    }

    /**
     * @param array $geojsonArray
     *
     * @return string
     */
    private function genGeoJSONString(array $geojsonArray) : string
    {
        return json_encode($geojsonArray);
    }
}