<?php

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryException;

use Brick\Geo\Geometry;
use Brick\Geo\Point;
use Brick\Geo\LineString;
use Brick\Geo\Polygon;
use Brick\Geo\Triangle;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPolygon;
use Brick\Geo\GeometryCollection;
use Brick\Geo\PolyhedralSurface;
use Brick\Geo\TIN;

/**
 * Converter class from Geometry to WKB.
 */
abstract class WkbWriter
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
            return self::writePoint($geometry);
        }
        if ($geometry instanceof LineString) {
            return self::writeLineString($geometry);
        }
        if ($geometry instanceof Polygon) {
            return self::writePolygon($geometry);
        }
        if ($geometry instanceof MultiPoint) {
            return self::writeMultiPoint($geometry);
        }
        if ($geometry instanceof MultiLineString) {
            return self::writeMultiLineString($geometry);
        }
        if ($geometry instanceof MultiPolygon) {
            return self::writeMultiPolygon($geometry);
        }
        if ($geometry instanceof GeometryCollection) {
            return self::writeGeometryCollection($geometry);
        }
        if ($geometry instanceof PolyhedralSurface) {
            return self::writePolyhedralSurface($geometry);
        }
        if ($geometry instanceof TIN) {
            return self::writeTIN($geometry);
        }
        if ($geometry instanceof Triangle) {
            return self::writeTriangle($geometry);
        }

        throw GeometryException::unsupportedGeometryType($geometry);
    }

    /**
     * @return string
     */
    protected static function packByteOrder()
    {
        $byteOrder = WkbTools::getMachineByteOrder();

        return self::packByte($byteOrder);
    }

    /**
     * @param integer $byte
     *
     * @return string
     */
    protected static function packByte($byte)
    {
        return pack('c', $byte);
    }

    /**
     * @param integer $uint
     *
     * @return string
     */
    protected static function packUnsignedInteger($uint)
    {
        return pack('L', $uint);
    }

    /**
     * @param float $double
     *
     * @return string
     */
    protected static function packDouble($double)
    {
        return pack('d', $double);
    }

    /**
     * @param \Brick\Geo\Point $point
     *
     * @return string
     */
    protected static function packPoint(Point $point)
    {
        $binary = self::packDouble($point->x());
        $binary.= self::packDouble($point->y());

        return $binary;
    }

    /**
     * @param \Brick\Geo\LineString $lineString
     *
     * @return string
     */
    protected static function packLineString(LineString $lineString)
    {
        $wkb = self::packUnsignedInteger($lineString->count());

        foreach ($lineString as $point) {
            $wkb .= self::packPoint($point);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\Point $point
     *
     * @return string
     */
    protected static function writePoint(Point $point)
    {
        $wkb = self::packByteOrder();
        $wkb.= self::packUnsignedInteger(Geometry::POINT);
        $wkb.= self::packPoint($point);

        return $wkb;
    }

    /**
     * @param \Brick\Geo\LineString $lineString
     *
     * @return string
     */
    protected static function writeLineString(LineString $lineString)
    {
        $wkb = self::packByteOrder();
        $wkb.= self::packUnsignedInteger(Geometry::LINESTRING);
        $wkb.= self::packLineString($lineString);

        return $wkb;
    }

    /**
     * @param \Brick\Geo\Polygon $polygon
     *
     * @return string
     */
    protected static function writePolygon(Polygon $polygon)
    {
        $wkb = self::packByteOrder();
        $wkb.= self::packUnsignedInteger(Geometry::POLYGON);
        $wkb.= self::packUnsignedInteger($polygon->count());

        foreach ($polygon as $ring) {
            $wkb .= self::packLineString($ring);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\Triangle $triangle
     *
     * @return string
     */
    protected static function writeTriangle(Triangle $triangle)
    {
        $wkb = self::packByteOrder();
        $wkb.= self::packUnsignedInteger(Geometry::TRIANGLE);
        $wkb.= self::packUnsignedInteger($triangle->count());

        foreach ($triangle as $ring) {
            $wkb .= self::packLineString($ring);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\MultiPoint $multiPoint
     *
     * @return string
     */
    protected static function writeMultiPoint(MultiPoint $multiPoint)
    {
        $wkb = self::packByteOrder();
        $wkb.= self::packUnsignedInteger(Geometry::MULTIPOINT);
        $wkb.= self::packUnsignedInteger($multiPoint->count());

        foreach ($multiPoint as $point) {
            $wkb .= self::writePoint($point);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\MultiLineString $multiLineString
     *
     * @return string
     */
    protected static function writeMultiLineString(MultiLineString $multiLineString)
    {
        $wkb = self::packByteOrder();
        $wkb.= self::packUnsignedInteger(Geometry::MULTILINESTRING);
        $wkb.= self::packUnsignedInteger($multiLineString->count());

        foreach ($multiLineString as $lineString) {
            $wkb .= self::writeLineString($lineString);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\MultiPolygon $multiPolygon
     *
     * @return string
     */
    protected static function writeMultiPolygon(MultiPolygon $multiPolygon)
    {
        $wkb = self::packByteOrder();
        $wkb.= self::packUnsignedInteger(Geometry::MULTIPOLYGON);
        $wkb.= self::packUnsignedInteger($multiPolygon->count());

        foreach ($multiPolygon as $polygon) {
            $wkb .= self::writePolygon($polygon);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\GeometryCollection $collection
     *
     * @return string
     */
    protected static function writeGeometryCollection(GeometryCollection $collection)
    {
        $wkb = self::packByteOrder();
        $wkb.= self::packUnsignedInteger(Geometry::GEOMETRYCOLLECTION);
        $wkb.= self::packUnsignedInteger($collection->count());

        foreach ($collection as $geometry) {
            $wkb .= self::write($geometry);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\PolyhedralSurface $surface
     *
     * @return string
     */
    protected static function writePolyhedralSurface(PolyhedralSurface $surface)
    {
        $wkb = self::packByteOrder();
        $wkb.= self::packUnsignedInteger(Geometry::POLYHEDRALSURFACE);
        $wkb.= self::packUnsignedInteger($surface->count());

        foreach ($surface as $polygon) {
            $wkb .= self::writePolygon($polygon);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\TIN $tin
     *
     * @return string
     */
    protected static function writeTIN(TIN $tin)
    {
        $wkb = self::packByteOrder();
        $wkb.= self::packUnsignedInteger(Geometry::TIN);
        $wkb.= self::packUnsignedInteger($tin->count());

        foreach ($tin as $polygon) {
            $wkb .= self::writePolygon($polygon);
        }

        return $wkb;
    }
}
