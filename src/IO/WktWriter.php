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
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public static function write(Geometry $geometry)
    {
        if ($geometry instanceof Point) {
            $type = 'POINT';
            $data = self::writePoint($geometry);
        } elseif ($geometry instanceof LineString) {
            $type = 'LINESTRING';
            $data = self::writeLineString($geometry);
        } elseif ($geometry instanceof Polygon) {
            $type = 'POLYGON';
            $data = self::writePolygon($geometry);
        } elseif ($geometry instanceof MultiPoint) {
            $type = 'MULTIPOINT';
            $data = self::writeMultiPoint($geometry);
        } elseif ($geometry instanceof MultiLineString) {
            $type = 'MULTILINESTRING';
            $data = self::writeMultiLineString($geometry);
        } elseif ($geometry instanceof MultiPolygon) {
            $type = 'MULTIPOLYGON';
            $data = self::writeMultiPolygon($geometry);
        } elseif ($geometry instanceof GeometryCollection) {
            $type = 'GEOMETRYCOLLECTION';
            $data = self::writeGeometryCollection($geometry);
        } else {
            throw GeometryException::unsupportedGeometryType($geometry);
        }

        $z = $geometry->is3D();
        $m = $geometry->isMeasured();

        $wkt = $type . ' ';

        if ($z) {
            $wkt .= 'Z';
        }
        if ($m) {
            $wkt .= 'M';
        }

        if ($z || $m) {
            $wkt .= ' ';
        }

        $wkt .= '(' . $data . ')';

        return $wkt;
    }

    /**
     * @param \Brick\Geo\Point $point
     *
     * @return string
     */
    private static function writePoint(Point $point)
    {
        $result = $point->x() . ' ' . $point->y();

        if (null !== $z = $point->z()) {
            $result .= ' ' . $z;
        }

        if (null !== $m = $point->m()) {
            $result .= ' ' . $m;
        }

        return $result;
    }

    /**
     * @param \Brick\Geo\LineString $lineString
     *
     * @return string
     */
    private static function writeLineString(LineString $lineString)
    {
        $result = [];
        foreach ($lineString as $point) {
            $result[] = self::writePoint($point);
        }

        return implode(', ', $result);
    }

    /**
     * @param \Brick\Geo\Polygon $polygon
     *
     * @return string
     */
    private static function writePolygon(Polygon $polygon)
    {
        $result = [];
        foreach ($polygon as $ring) {
            $result[] = '(' . self::writeLineString($ring) . ')';
        }

        return implode(', ', $result);
    }

    /**
     * @param \Brick\Geo\MultiPoint $multiPoint
     *
     * @return string
     */
    private static function writeMultiPoint(MultiPoint $multiPoint)
    {
        $result = [];
        foreach ($multiPoint as $point) {
            $result[] = self::writePoint($point);
        }

        return implode(', ', $result);
    }

    /**
     * @param \Brick\Geo\MultiLineString $multiLineString
     *
     * @return string
     */
    private static function writeMultiLineString(MultiLineString $multiLineString)
    {
        $result = [];
        foreach ($multiLineString as $lineString) {
            $result[] = '(' . self::writeLineString($lineString) . ')';
        }

        return implode(', ', $result);
    }

    /**
     * @param \Brick\Geo\MultiPolygon $multiPolygon
     *
     * @return string
     */
    private static function writeMultiPolygon(MultiPolygon $multiPolygon)
    {
        $result = [];
        foreach ($multiPolygon as $polygon) {
            $result[] = '(' . self::writePolygon($polygon) . ')';
        }

        return implode(', ', $result);
    }

    /**
     * @param \Brick\Geo\GeometryCollection $collection
     *
     * @return string
     */
    private static function writeGeometryCollection(GeometryCollection $collection)
    {
        $result = [];
        foreach ($collection as $geometry) {
            $result[] = self::write($geometry);
        }

        return implode(', ', $result);
    }
}
