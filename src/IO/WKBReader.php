<?php

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryException;
use Brick\Geo\Geometry;
use Brick\Geo\Point;
use Brick\Geo\LineString;
use Brick\Geo\Polygon;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPolygon;
use Brick\Geo\GeometryCollection;
use Brick\Geo\PolyhedralSurface;
use Brick\Geo\TIN;

/**
 * Builds geometries out of Well-Known Binary strings.
 */
class WKBReader
{
    /**
     * @param string $wkb
     *
     * @return \Brick\Geo\Geometry
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function read($wkb)
    {
        $buffer = new WKBBuffer($wkb);
        $geometry = $this->readGeometry($buffer);

        if (! $buffer->isEndOfStream()) {
            throw GeometryException::invalidWkb('unexpected data at end of stream');
        }

        return $geometry;
    }

    /**
     * @param WKBBuffer $buffer
     *
     * @return \Brick\Geo\Geometry
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    protected function readGeometry(WKBBuffer $buffer)
    {
        $buffer->readByteOrder();
        $wkbType = $buffer->readUnsignedLong();

        $geometryType = $wkbType % 1000;
        $dimension = ($wkbType - $geometryType) / 1000;

        if ($dimension < 0 || $dimension > 3) {
            throw GeometryException::unsupportedWkbType($wkbType);
        }

        $is3D = ($dimension === 1 || $dimension === 3);
        $isMeasured = ($dimension === 2 || $dimension === 3);

        switch ($geometryType) {
            case Geometry::POINT:
                return $this->readPoint($buffer, $is3D, $isMeasured);
            case Geometry::LINESTRING:
                return $this->readLineString($buffer, $is3D, $isMeasured);
            case Geometry::POLYGON:
            case Geometry::TRIANGLE:
                return $this->readPolygon($buffer, $is3D, $isMeasured);
            case Geometry::MULTIPOINT:
                return $this->readMultiPoint($buffer, $is3D, $isMeasured);
            case Geometry::MULTILINESTRING:
                return $this->readMultiLineString($buffer, $is3D, $isMeasured);
            case Geometry::MULTIPOLYGON:
                return $this->readMultiPolygon($buffer, $is3D, $isMeasured);
            case Geometry::GEOMETRYCOLLECTION:
                return $this->readGeometryCollection($buffer, $is3D, $isMeasured);
            case Geometry::POLYHEDRALSURFACE:
                return $this->readPolyhedralSurface($buffer, $is3D, $isMeasured);
            case Geometry::TIN:
                return $this->readTIN($buffer, $is3D, $isMeasured);
        }

        throw GeometryException::unsupportedWkbType($wkbType);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     *
     * @return \Brick\Geo\Point
     */
    private function readPoint(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $count = 2 + ($is3D ? 1 : 0) + ($isMeasured ? 1 : 0);
        $values = $buffer->readDoubles($count);

        $coords = [$values[1], $values[2]];

        if ($is3D || $isMeasured) {
            $coords[] = $is3D ? $values[3] : null;

            if ($isMeasured) {
                $coords[] = $values[$is3D ? 4 : 3];
            }
        }

        return call_user_func_array([Point::class, 'factory'], $coords);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     *
     * @return \Brick\Geo\LineString
     */
    private function readLineString(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numPoints = $buffer->readUnsignedLong();
        $points = [];

        for ($i=0; $i<$numPoints; $i++) {
            $points[] = $this->readPoint($buffer, $is3D, $isMeasured);
        }

        return LineString::factory($points);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     *
     * @return \Brick\Geo\Polygon
     */
    private function readPolygon(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numRings = $buffer->readUnsignedLong();
        $rings = [];

        for ($i=0; $i<$numRings; $i++) {
            $rings[] = $this->readLineString($buffer, $is3D, $isMeasured);
        }

        return Polygon::factory($rings);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     *
     * @return \Brick\Geo\MultiPoint
     */
    private function readMultiPoint(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numPoints = $buffer->readUnsignedLong();
        $points = [];

        for ($i=0; $i<$numPoints; $i++) {
            $points[] = $this->readGeometry($buffer, $is3D, $isMeasured);
        }

        return MultiPoint::factory($points);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     *
     * @return \Brick\Geo\MultiLineString
     */
    private function readMultiLineString(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numLineStrings = $buffer->readUnsignedLong();
        $lineStrings = [];

        for ($i=0; $i<$numLineStrings; $i++) {
            $lineStrings[] = $this->readGeometry($buffer, $is3D, $isMeasured);
        }

        return MultiLineString::factory($lineStrings);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     *
     * @return \Brick\Geo\MultiPolygon
     */
    private function readMultiPolygon(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numPolygons = $buffer->readUnsignedLong();
        $polygons = [];

        for ($i=0; $i<$numPolygons; $i++) {
            $polygons[] = $this->readGeometry($buffer, $is3D, $isMeasured);
        }

        return MultiPolygon::factory($polygons);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     *
     * @return \Brick\Geo\GeometryCollection
     */
    private function readGeometryCollection(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numGeometries = $buffer->readUnsignedLong();
        $geometries = [];

        for ($i=0; $i<$numGeometries; $i++) {
            $geometries[] = $this->readGeometry($buffer, $is3D, $isMeasured);
        }

        return GeometryCollection::factory($geometries);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     *
     * @return \Brick\Geo\PolyhedralSurface
     */
    private function readPolyhedralSurface(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numPolygons = $buffer->readUnsignedLong();
        $polygons = [];

        for ($i=0; $i<$numPolygons; $i++) {
            $polygons[] = $this->readGeometry($buffer, $is3D, $isMeasured);
        }

        return PolyhedralSurface::factory($polygons);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     *
     * @return \Brick\Geo\TIN
     */
    private function readTIN(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numPolygons = $buffer->readUnsignedLong();
        $polygons = [];

        for ($i=0; $i<$numPolygons; $i++) {
            $polygons[] = $this->readGeometry($buffer, $is3D, $isMeasured);
        }

        return TIN::factory($polygons);
    }
}