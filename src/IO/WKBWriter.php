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
abstract class WKBWriter
{
    /**
     * @param \Brick\Geo\Geometry $geometry
     * @param integer|null        $byteOrder
     *
     * @return string
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public static function write(Geometry $geometry, $byteOrder = null)
    {
        if ($byteOrder === null) {
            $byteOrder = WKBTools::getMachineByteOrder();
        }

        if ($geometry instanceof Point) {
            return self::writePoint($geometry, $byteOrder);
        }
        if ($geometry instanceof LineString) {
            return self::writeLineString($geometry, $byteOrder);
        }
        if ($geometry instanceof Polygon) {
            return self::writePolygon($geometry, $byteOrder);
        }
        if ($geometry instanceof MultiPoint) {
            return self::writeMultiPoint($geometry, $byteOrder);
        }
        if ($geometry instanceof MultiLineString) {
            return self::writeMultiLineString($geometry, $byteOrder);
        }
        if ($geometry instanceof MultiPolygon) {
            return self::writeMultiPolygon($geometry, $byteOrder);
        }
        if ($geometry instanceof GeometryCollection) {
            return self::writeGeometryCollection($geometry, $byteOrder);
        }
        if ($geometry instanceof PolyhedralSurface) {
            return self::writePolyhedralSurface($geometry, $byteOrder);
        }
        if ($geometry instanceof TIN) {
            return self::writeTIN($geometry, $byteOrder);
        }
        if ($geometry instanceof Triangle) {
            return self::writeTriangle($geometry, $byteOrder);
        }

        throw GeometryException::unsupportedGeometryType($geometry);
    }

    /**
     * @param integer $byte
     *
     * @return string
     */
    private static function packByte($byte)
    {
        return pack('C', $byte);
    }

    /**
     * @param integer $uint
     * @param integer $byteOrder
     *
     * @return string
     */
    private static function packUnsignedInteger($uint, $byteOrder)
    {
        return pack($byteOrder === WKBTools::BIG_ENDIAN ? 'N' : 'V', $uint);
    }

    /**
     * @param float   $double
     * @param integer $byteOrder
     *
     * @return string
     */
    private static function packDouble($double, $byteOrder)
    {
        $binary = pack('d', $double);

        if ($byteOrder !== WKBTools::getMachineByteOrder()) {
            return strrev($binary);
        }

        return $binary;
    }

    /**
     * @param integer  $geometryType
     * @param Geometry $geometry
     * @param integer  $byteOrder
     *
     * @return string
     */
    private static function packGeometryTypeZM($geometryType, Geometry $geometry, $byteOrder)
    {
        if ($geometry->is3D()) {
            $geometryType += 1000;
        }

        if ($geometry->isMeasured()) {
            $geometryType += 2000;
        }

        return self::packUnsignedInteger($geometryType, $byteOrder);
    }

    /**
     * @param \Brick\Geo\Point $point
     * @param integer          $byteOrder
     *
     * @return string
     */
    private static function packPoint(Point $point, $byteOrder)
    {
        $binary = self::packDouble($point->x(), $byteOrder) . self::packDouble($point->y(), $byteOrder);

        if (null !== $z = $point->z()) {
            $binary .= self::packDouble($z, $byteOrder);
        }
        if (null !== $m = $point->m()) {
            $binary .= self::packDouble($m, $byteOrder);
        }

        return $binary;
    }

    /**
     * @param \Brick\Geo\LineString $lineString
     * @param integer               $byteOrder
     *
     * @return string
     */
    private static function packLineString(LineString $lineString, $byteOrder)
    {
        $wkb = self::packUnsignedInteger($lineString->count(), $byteOrder);

        foreach ($lineString as $point) {
            $wkb .= self::packPoint($point, $byteOrder);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\Point $point
     * @param integer          $byteOrder
     *
     * @return string
     */
    private static function writePoint(Point $point, $byteOrder)
    {
        $wkb = self::packByte($byteOrder);
        $wkb.= self::packGeometryTypeZM(Geometry::POINT, $point, $byteOrder);
        $wkb.= self::packPoint($point, $byteOrder);

        return $wkb;
    }

    /**
     * @param \Brick\Geo\LineString $lineString
     * @param integer               $byteOrder
     *
     * @return string
     */
    private static function writeLineString(LineString $lineString, $byteOrder)
    {
        $wkb = self::packByte($byteOrder);
        $wkb.= self::packGeometryTypeZM(Geometry::LINESTRING, $lineString, $byteOrder);
        $wkb.= self::packLineString($lineString, $byteOrder);

        return $wkb;
    }

    /**
     * @param \Brick\Geo\Polygon $polygon
     * @param integer            $byteOrder
     *
     * @return string
     */
    private static function writePolygon(Polygon $polygon, $byteOrder)
    {
        $wkb = self::packByte($byteOrder);
        $wkb.= self::packGeometryTypeZM(Geometry::POLYGON, $polygon, $byteOrder);
        $wkb.= self::packUnsignedInteger($polygon->count(), $byteOrder);

        foreach ($polygon as $ring) {
            $wkb .= self::packLineString($ring, $byteOrder);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\Triangle $triangle
     * @param integer             $byteOrder
     *
     * @return string
     */
    private static function writeTriangle(Triangle $triangle, $byteOrder)
    {
        $wkb = self::packByte($byteOrder);
        $wkb.= self::packGeometryTypeZM(Geometry::TRIANGLE, $triangle, $byteOrder);
        $wkb.= self::packUnsignedInteger($triangle->count(), $byteOrder);

        foreach ($triangle as $ring) {
            $wkb .= self::packLineString($ring, $byteOrder);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\MultiPoint $multiPoint
     * @param integer               $byteOrder
     *
     * @return string
     */
    private static function writeMultiPoint(MultiPoint $multiPoint, $byteOrder)
    {
        $wkb = self::packByte($byteOrder);
        $wkb.= self::packGeometryTypeZM(Geometry::MULTIPOINT, $multiPoint, $byteOrder);
        $wkb.= self::packUnsignedInteger($multiPoint->count(), $byteOrder);

        foreach ($multiPoint as $point) {
            $wkb .= self::writePoint($point, $byteOrder);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\MultiLineString $multiLineString
     * @param integer                    $byteOrder
     *
     * @return string
     */
    private static function writeMultiLineString(MultiLineString $multiLineString, $byteOrder)
    {
        $wkb = self::packByte($byteOrder);
        $wkb.= self::packGeometryTypeZM(Geometry::MULTILINESTRING, $multiLineString, $byteOrder);
        $wkb.= self::packUnsignedInteger($multiLineString->count(), $byteOrder);

        foreach ($multiLineString as $lineString) {
            $wkb .= self::writeLineString($lineString, $byteOrder);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\MultiPolygon $multiPolygon
     * @param integer                 $byteOrder
     *
     * @return string
     */
    private static function writeMultiPolygon(MultiPolygon $multiPolygon, $byteOrder)
    {
        $wkb = self::packByte($byteOrder);
        $wkb.= self::packGeometryTypeZM(Geometry::MULTIPOLYGON, $multiPolygon, $byteOrder);
        $wkb.= self::packUnsignedInteger($multiPolygon->count(), $byteOrder);

        foreach ($multiPolygon as $polygon) {
            $wkb .= self::writePolygon($polygon, $byteOrder);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\GeometryCollection $collection
     * @param integer                       $byteOrder
     *
     * @return string
     */
    private static function writeGeometryCollection(GeometryCollection $collection, $byteOrder)
    {
        $wkb = self::packByte($byteOrder);
        $wkb.= self::packGeometryTypeZM(Geometry::GEOMETRYCOLLECTION, $collection, $byteOrder);
        $wkb.= self::packUnsignedInteger($collection->count(), $byteOrder);

        foreach ($collection as $geometry) {
            $wkb .= self::write($geometry, $byteOrder);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\PolyhedralSurface $surface
     * @param integer                      $byteOrder
     *
     * @return string
     */
    private static function writePolyhedralSurface(PolyhedralSurface $surface, $byteOrder)
    {
        $wkb = self::packByte($byteOrder);
        $wkb.= self::packGeometryTypeZM(Geometry::POLYHEDRALSURFACE, $surface, $byteOrder);
        $wkb.= self::packUnsignedInteger($surface->count(), $byteOrder);

        foreach ($surface as $polygon) {
            $wkb .= self::writePolygon($polygon, $byteOrder);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\TIN $tin
     * @param integer        $byteOrder
     *
     * @return string
     */
    private static function writeTIN(TIN $tin, $byteOrder)
    {
        $wkb = self::packByte($byteOrder);
        $wkb.= self::packGeometryTypeZM(Geometry::TIN, $tin, $byteOrder);
        $wkb.= self::packUnsignedInteger($tin->count(), $byteOrder);

        foreach ($tin as $polygon) {
            $wkb .= self::writePolygon($polygon, $byteOrder);
        }

        return $wkb;
    }
}
