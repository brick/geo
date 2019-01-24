<?php

declare(strict_types = 1);

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;
use Brick\Geo\GeometryCollection;

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
        if ($geometry instanceof GeometryCollection
            // Filter out MultiPoint, MultiLineString and MultiPolygon
            && $geometry->geometryType() === 'GeometryCollection') {
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
        $geometryType = $geometry->geometryType();
        $validGeometries = [
            'Point',
            'MultiPoint',
            'LineString',
            'MultiLineString',
            'Polygon',
            'MultiPolygon'
        ];

        if (! in_array($geometryType, $validGeometries)) {
            throw GeometryIOException::unsupportedGeometryType($geometry->geometryType());
        }

        return [
            'type' => $geometryType,
            'coordinates' => $geometry->toArray()
        ];
    }

    /**
     * @param GeometryCollection $geometryCollection
     *
     * @return string
     *
     * @throws GeometryIOException
     */
    private function writeFeatureCollection(GeometryCollection $geometryCollection) : string
    {
        $geojsonArray = [
            'type' => 'FeatureCollection',
            'features' => []
        ];

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
