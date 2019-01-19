<?php

declare(strict_types = 1);

namespace Brick\Geo\IO;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;
use Brick\Geo\LineString;
use Brick\Geo\Point;
use Brick\Geo\Polygon;

abstract class AbstractGeoJSONReader
{
    /**
     * @param GeoJSONParser $parser
     * @param int           $srid
     *
     * @return Geometry
     *
     * @throws GeometryIOException
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     */
    protected function readGeometry(GeoJSONParser $parser, int $srid) : Geometry
    {
        $geometryType = $parser->getGeometryType();
        $geometryCoordinates = $parser->getGeometryCoordinates();

        $hasZ = false;
        $hasM = false;
        $isEmpty = empty($geometryCoordinates);

        $cs = new CoordinateSystem($hasZ, $hasM, $srid);

        switch ($geometryType) {
            case 'POINT':
                if ($isEmpty) {
                    return new Point($cs);
                }

                return $this->readPointType($parser, $cs);

            case 'LINESTRING':
                if ($isEmpty) {
                    return new LineString($cs);
                }

                return $this->readLineStringType($parser, $cs);

            case 'POLYGON':
                if ($isEmpty) {
                    return new Polygon($cs);
                }

                return $this->readPolygonType($parser, $cs);
        }

        throw new GeometryIOException('Unknown geometry type: ' . $geometryType);
    }

    /**
     * [x, y]
     *
     * @param GeoJSONParser    $parser
     * @param CoordinateSystem $cs
     *
     * @return Point
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     */
    private function readPointType(GeoJSONParser $parser, CoordinateSystem $cs) : Point
    {
        $coords = $parser->getGeometryCoordinates();

        return new Point($cs, ...$coords);
    }

    /**
     * [[x, y], ...]
     *
     * @param GeoJSONParser    $parser
     * @param CoordinateSystem $cs
     *
     * @return LineString
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     */
    private function readLineStringType(GeoJSONParser $parser, CoordinateSystem $cs) : LineString
    {
        $points = [];

        foreach ($parser->getGeometryCoordinates() as $coords) {
            $points[] = new Point($cs, ...$coords);
        }

        return new LineString($cs, ...$points);
    }

    /**
     * [[[x, y], ...], ...]
     *
     * @param GeoJSONParser    $parser
     * @param CoordinateSystem $cs
     *
     * @return Polygon
     *
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     * @throws \Brick\Geo\Exception\CoordinateSystemException
     */
    private function readPolygonType(GeoJSONParser $parser, CoordinateSystem $cs) : Polygon
    {
        $lineStrings = [];

        foreach ($parser->getGeometryCoordinates() as $polygonCoords) {

            $points = [];
            foreach ($polygonCoords as $coords) {
                $points[] = new Point($cs, ...$coords);
            }

            $lineStrings[] = new LineString($cs, ...$points);
        }

        return new Polygon($cs, ...$lineStrings);
    }
}