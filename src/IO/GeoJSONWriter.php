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
        if ($geometry instanceof Point) {
            return $this->writePoint($geometry);
        } elseif ($geometry instanceof MultiPoint) {
            return $this->writeMultiPoint($geometry);
        } elseif ($geometry instanceof LineString) {
            return $this->writeLineString($geometry);
        } elseif ($geometry instanceof MultiLineString) {
            return $this->writeMultiLineString($geometry);
        } elseif ($geometry instanceof Polygon) {
            return $this->writePolygon($geometry);
        } elseif ($geometry instanceof MultiPolygon) {
            return $this->writeMultiPolygon($geometry);
        } elseif ($geometry instanceof GeometryCollection) {
            return $this->writeFeatureCollection($geometry);
        }

        throw GeometryIOException::unsupportedGeometryType($geometry->geometryType());
    }

    /**
     * @param Point $geometry
     *
     * @return string
     */
    private function writePoint(Point $geometry)
    {
        $geojsonArray = [
            'type' => 'Point',
            'coordinates' => $geometry->toArray()
        ];

        return $this->genGeoJSONString($geojsonArray);
    }

    /**
     * @param MultiPoint $geometry
     *
     * @return string
     */
    private function writeMultiPoint(MultiPoint $geometry)
    {
        $geojsonArray = [
            'type' => 'MultiPoint',
            'coordinates' => $geometry->toArray()
        ];

        return $this->genGeoJSONString($geojsonArray);
    }

    /**
     * @param LineString $geometry
     *
     * @return string
     */
    private function writeLineString(LineString $geometry)
    {
        $geojsonArray = [
            'type' => 'LineString',
            'coordinates' => $geometry->toArray()
        ];

        return $this->genGeoJSONString($geojsonArray);
    }

    /**
     * @param MultiLineString $geometry
     *
     * @return string
     */
    private function writeMultiLineString(MultiLineString $geometry)
    {
        $geojsonArray = [
            'type' => 'MultiLineString',
            'coordinates' => $geometry->toArray()
        ];

        return $this->genGeoJSONString($geojsonArray);
    }

    /**
     * @param Polygon $geometry
     *
     * @return string
     */
    private function writePolygon(Polygon $geometry)
    {
        $geojsonArray = [
            'type' => 'Polygon',
            'coordinates' => $geometry->toArray()
        ];

        return $this->genGeoJSONString($geojsonArray);
    }

    /**
     * @param MultiPolygon $geometry
     *
     * @return string
     */
    private function writeMultiPolygon(MultiPolygon $geometry)
    {
        $geojsonArray = [
            'type' => 'MultiPolygon',
            'coordinates' => $geometry->toArray()
        ];

        return $this->genGeoJSONString($geojsonArray);
    }

    /**
     * @param GeometryCollection $geometryCollection
     *
     * @return string
     * @throws GeometryIOException
     */
    private function writeFeatureCollection(GeometryCollection $geometryCollection)
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
                'geometry' => json_decode($this->write($geometry))
            ];
        }

        return $this->genGeoJSONString($geojsonArray);
    }

    /**
     * @param array $geojsonArray
     *
     * @return string
     */
    private function genGeoJSONString(array $geojsonArray)
    {
        return json_encode($geojsonArray);
    }
}