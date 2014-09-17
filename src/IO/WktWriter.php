<?php

namespace Brick\Geo\IO;

use Brick\Geo\GeometryException;
use Brick\Geo\Geometry;
use Brick\Geo\Point;
use Brick\Geo\LineString;
use Brick\Geo\Polygon;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPolygon;
use Brick\Geo\GeometryCollection;

/**
 * Converter class from Geometry to WKT.
 */
abstract class WktWriter
{
    /**
     * @param \Brick\Geo\Geometry $geometry
     *
     * @return string
     *
     * @throws \Brick\Geo\GeometryException
     */
    public static function write(Geometry $geometry)
    {
        if ($geometry instanceof Point) {
            return sprintf('POINT(%s)', self::writePoint($geometry));
        }
        if ($geometry instanceof LineString) {
            return sprintf('LINESTRING(%s)', self::writeLineString($geometry));
        }
        if ($geometry instanceof Polygon) {
            return sprintf('POLYGON(%s)', self::writePolygon($geometry));
        }
        if ($geometry instanceof MultiPoint) {
            return sprintf('MULTIPOINT(%s)', self::writeMultiPoint($geometry));
        }
        if ($geometry instanceof MultiLineString) {
            return sprintf('MULTILINESTRING(%s)', self::writeMultiLineString($geometry));
        }
        if ($geometry instanceof MultiPolygon) {
            return sprintf('MULTIPOLYGON(%s)', self::writeMultiPolygon($geometry));
        }
        if ($geometry instanceof GeometryCollection) {
            return sprintf('GEOMETRYCOLLECTION(%s)', self::writeGeometryCollection($geometry));
        }

        throw GeometryException::unsupportedGeometryType($geometry);
    }

    /**
     * @param \Brick\Geo\Point $point
     *
     * @return string
     */
    protected static function writePoint(Point $point)
    {
        return sprintf('%s %s', $point->x(), $point->y());
    }

    /**
     * @param \Brick\Geo\LineString $lineString
     *
     * @return string
     */
    protected static function writeLineString(LineString $lineString)
    {
        $result = [];
        foreach ($lineString as $point) {
            $result[] = self::writePoint($point);
        }

        return implode(',', $result);
    }

    /**
     * @param \Brick\Geo\Polygon $polygon
     *
     * @return string
     */
    protected static function writePolygon(Polygon $polygon)
    {
        $result = [];
        foreach ($polygon as $ring) {
            $result[] = '(' . self::writeLineString($ring) . ')';
        }

        return implode(',', $result);
    }

    /**
     * @param \Brick\Geo\MultiPoint $multiPoint
     *
     * @return string
     */
    protected static function writeMultiPoint(MultiPoint $multiPoint)
    {
        $result = [];
        foreach ($multiPoint as $point) {
            $result[] = self::writePoint($point);
        }

        return implode(',', $result);
    }

    /**
     * @param \Brick\Geo\MultiLineString $multiLineString
     *
     * @return string
     */
    protected static function writeMultiLineString(MultiLineString $multiLineString)
    {
        $result = [];
        foreach ($multiLineString as $lineString) {
            $result[] = '(' . self::writeLineString($lineString) . ')';
        }

        return implode(',', $result);
    }

    /**
     * @param \Brick\Geo\MultiPolygon $multiPolygon
     *
     * @return string
     */
    protected static function writeMultiPolygon(MultiPolygon $multiPolygon)
    {
        $result = [];
        foreach ($multiPolygon as $polygon) {
            $result[] = '(' . self::writePolygon($polygon) . ')';
        }

        return implode(',', $result);
    }

    /**
     * @param \Brick\Geo\GeometryCollection $collection
     *
     * @return string
     */
    protected static function writeGeometryCollection(GeometryCollection $collection)
    {
        $result = [];
        foreach ($collection as $geometry) {
            $result[] = self::write($geometry);
        }

        return implode(',', $result);
    }
}
