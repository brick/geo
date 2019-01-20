<?php

declare(strict_types = 1);

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;
use Brick\Geo\MultiPoint;
use Brick\Geo\Point;

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
            'coordinates' => $this->genPointCoords($geometry)
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
            'coordinates' => $this->genMultiPointCoords($geometry)
        ];

        return $this->genGeoJSONString($geojsonArray);
    }

    /**
     * @param Point $geometry
     *
     * @return array
     */
    private function genPointCoords(Point $geometry) : array
    {
        if ($geometry->isEmpty()) {
            return [];
        } elseif ($geometry->is3D()) {
            return [$geometry->x(), $geometry->y(), $geometry->z()];
        } else {
            return [$geometry->x(), $geometry->y()];
        }
    }

    /**
     * @param MultiPoint $geometry
     *
     * @return array
     */
    private function genMultiPointCoords(MultiPoint $geometry) : array
    {
        $coords = [];

        if ($geometry->isEmpty()) {
            return $coords;
        }

        /**
         * @var Point $point
         */
        foreach ($geometry->geometries() as $point) {
            $coords[] = $this->genPointCoords($point);
        }

        return $coords;
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