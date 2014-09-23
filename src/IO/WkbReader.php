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
abstract class WkbReader
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
        $buffer = new WkbBuffer($wkb);
        $geometry = self::readGeometry($buffer);

        if (! $buffer->isEndOfStream()) {
            throw GeometryException::invalidWkb();
        }

        return $geometry;
    }

    /**
     * @param WkbBuffer $buffer
     *
     * @return \Brick\Geo\Geometry
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    protected static function readGeometry(WkbBuffer $buffer)
    {
        $buffer->readByteOrder();
        $wkbType = $buffer->readUnsignedInteger();

        switch ($wkbType) {
            case Geometry::POINT:
                return self::readPoint($buffer);
            case Geometry::LINESTRING:
                return self::readLineString($buffer);
            case Geometry::POLYGON:
            case Geometry::TRIANGLE:
                return self::readPolygon($buffer);
            case Geometry::MULTIPOINT:
                return self::readMultiPoint($buffer);
            case Geometry::MULTILINESTRING:
                return self::readMultiLineString($buffer);
            case Geometry::MULTIPOLYGON:
                return self::readMultiPolygon($buffer);
            case Geometry::GEOMETRYCOLLECTION:
                return self::readGeometryCollection($buffer);
            case Geometry::POLYHEDRALSURFACE:
                return self::readPolyhedralSurface($buffer);
            case Geometry::TIN:
                return self::readTIN($buffer);
        }

        throw GeometryException::unsupportedWkbType($wkbType);
    }

    /**
     * @param WkbBuffer $buffer
     *
     * @return \Brick\Geo\Point
     */
    private static function readPoint(WkbBuffer $buffer)
    {
        $x = $buffer->readDouble();
        $y = $buffer->readDouble();

        return Point::factory($x, $y);
    }

    /**
     * @param WkbBuffer $buffer
     *
     * @return \Brick\Geo\LineString
     */
    private static function readLineString(WkbBuffer $buffer)
    {
        $numPoints = $buffer->readUnsignedInteger();
        $points = [];

        for ($i=0; $i<$numPoints; $i++) {
            $points[] = self::readPoint($buffer);
        }

        return LineString::factory($points);
    }

    /**
     * @param WkbBuffer $buffer
     *
     * @return \Brick\Geo\Polygon
     */
    private static function readPolygon(WkbBuffer $buffer)
    {
        $numRings = $buffer->readUnsignedInteger();
        $rings = [];

        for ($i=0; $i<$numRings; $i++) {
            $rings[] = self::readLineString($buffer);
        }

        return Polygon::factory($rings);
    }

    /**
     * @param WkbBuffer $buffer
     *
     * @return \Brick\Geo\MultiPoint
     */
    private static function readMultiPoint(WkbBuffer $buffer)
    {
        $numPoints = $buffer->readUnsignedInteger();
        $points = [];

        for ($i=0; $i<$numPoints; $i++) {
            $points[] = self::readGeometry($buffer);
        }

        return MultiPoint::factory($points);
    }

    /**
     * @param WkbBuffer $buffer
     *
     * @return \Brick\Geo\MultiLineString
     */
    private static function readMultiLineString(WkbBuffer $buffer)
    {
        $numLineStrings = $buffer->readUnsignedInteger();
        $lineStrings = [];

        for ($i=0; $i<$numLineStrings; $i++) {
            $lineStrings[] = self::readGeometry($buffer);
        }

        return MultiLineString::factory($lineStrings);
    }

    /**
     * @param WkbBuffer $buffer
     *
     * @return \Brick\Geo\MultiPolygon
     */
    private static function readMultiPolygon(WkbBuffer $buffer)
    {
        $numPolygons = $buffer->readUnsignedInteger();
        $polygons = [];

        for ($i=0; $i<$numPolygons; $i++) {
            $polygons[] = self::readGeometry($buffer);
        }

        return MultiPolygon::factory($polygons);
    }

    /**
     * @param WkbBuffer $buffer
     *
     * @return \Brick\Geo\GeometryCollection
     */
    private static function readGeometryCollection(WkbBuffer $buffer)
    {
        $numGeometries = $buffer->readUnsignedInteger();
        $geometries = [];

        for ($i=0; $i<$numGeometries; $i++) {
            $geometries[] = self::readGeometry($buffer);
        }

        return GeometryCollection::factory($geometries);
    }

    /**
     * @param WkbBuffer $buffer
     *
     * @return \Brick\Geo\PolyhedralSurface
     */
    private static function readPolyhedralSurface(WkbBuffer $buffer)
    {
        $numPolygons = $buffer->readUnsignedInteger();
        $polygons = [];

        for ($i=0; $i<$numPolygons; $i++) {
            $polygons[] = self::readGeometry($buffer);
        }

        return PolyhedralSurface::factory($polygons);
    }

    /**
     * @param WkbBuffer $buffer
     *
     * @return \Brick\Geo\TIN
     */
    private static function readTIN(WkbBuffer $buffer)
    {
        $numPolygons = $buffer->readUnsignedInteger();
        $polygons = [];

        for ($i=0; $i<$numPolygons; $i++) {
            $polygons[] = self::readGeometry($buffer);
        }

        return TIN::factory($polygons);
    }
}
