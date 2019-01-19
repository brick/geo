<?php

declare(strict_types = 1);

namespace Brick\Geo\IO;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;
use Brick\Geo\Point;

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

                return $this->readPoint($parser, $cs);
        }

        throw new GeometryIOException('Unknown geometry type: ' . $geometryType);
    }

    /**
     * x y
     *
     * @param GeoJSONParser    $parser
     * @param CoordinateSystem $cs
     *
     * @return Point
     * @throws \Brick\Geo\Exception\InvalidGeometryException
     */
    private function readPoint(GeoJSONParser $parser, CoordinateSystem $cs) : Point
    {
        $coords = $parser->getGeometryCoordinates();

        return new Point($cs, ...$coords);
    }
}