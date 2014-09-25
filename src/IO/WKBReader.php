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
abstract class WKBReader
{
    /**
     * @param string $wkb
     *
     * @return \Brick\Geo\Geometry
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public static function read($wkb)
    {
        $buffer = new WKBBuffer($wkb);
        $geometry = self::readGeometry($buffer);

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
    protected static function readGeometry(WKBBuffer $buffer)
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
                return self::readPoint($buffer, $is3D, $isMeasured);
            case Geometry::LINESTRING:
                return self::readLineString($buffer, $is3D, $isMeasured);
            case Geometry::POLYGON:
            case Geometry::TRIANGLE:
                return self::readPolygon($buffer, $is3D, $isMeasured);
            case Geometry::MULTIPOINT:
                return self::readMultiPoint($buffer, $is3D, $isMeasured);
            case Geometry::MULTILINESTRING:
                return self::readMultiLineString($buffer, $is3D, $isMeasured);
            case Geometry::MULTIPOLYGON:
                return self::readMultiPolygon($buffer, $is3D, $isMeasured);
            case Geometry::GEOMETRYCOLLECTION:
                return self::readGeometryCollection($buffer, $is3D, $isMeasured);
            case Geometry::POLYHEDRALSURFACE:
                return self::readPolyhedralSurface($buffer, $is3D, $isMeasured);
            case Geometry::TIN:
                return self::readTIN($buffer, $is3D, $isMeasured);
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
    private static function readPoint(WKBBuffer $buffer, $is3D, $isMeasured)
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
    private static function readLineString(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numPoints = $buffer->readUnsignedLong();
        $points = [];

        for ($i=0; $i<$numPoints; $i++) {
            $points[] = self::readPoint($buffer, $is3D, $isMeasured);
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
    private static function readPolygon(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numRings = $buffer->readUnsignedLong();
        $rings = [];

        for ($i=0; $i<$numRings; $i++) {
            $rings[] = self::readLineString($buffer, $is3D, $isMeasured);
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
    private static function readMultiPoint(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numPoints = $buffer->readUnsignedLong();
        $points = [];

        for ($i=0; $i<$numPoints; $i++) {
            $points[] = self::readGeometry($buffer, $is3D, $isMeasured);
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
    private static function readMultiLineString(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numLineStrings = $buffer->readUnsignedLong();
        $lineStrings = [];

        for ($i=0; $i<$numLineStrings; $i++) {
            $lineStrings[] = self::readGeometry($buffer, $is3D, $isMeasured);
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
    private static function readMultiPolygon(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numPolygons = $buffer->readUnsignedLong();
        $polygons = [];

        for ($i=0; $i<$numPolygons; $i++) {
            $polygons[] = self::readGeometry($buffer, $is3D, $isMeasured);
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
    private static function readGeometryCollection(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numGeometries = $buffer->readUnsignedLong();
        $geometries = [];

        for ($i=0; $i<$numGeometries; $i++) {
            $geometries[] = self::readGeometry($buffer, $is3D, $isMeasured);
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
    private static function readPolyhedralSurface(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numPolygons = $buffer->readUnsignedLong();
        $polygons = [];

        for ($i=0; $i<$numPolygons; $i++) {
            $polygons[] = self::readGeometry($buffer, $is3D, $isMeasured);
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
    private static function readTIN(WKBBuffer $buffer, $is3D, $isMeasured)
    {
        $numPolygons = $buffer->readUnsignedLong();
        $polygons = [];

        for ($i=0; $i<$numPolygons; $i++) {
            $polygons[] = self::readGeometry($buffer, $is3D, $isMeasured);
        }

        return TIN::factory($polygons);
    }
}
