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
class WKBWriter
{
    /**
     * The output byte order, BIG_ENDIAN or LITTLE_ENDIAN.
     *
     * @var integer
     */
    private $byteOrder;

    /**
     * @var integer
     */
    private $machineByteOrder;

    /**
     * @throws GeometryException
     */
    public function __construct()
    {
        $this->byteOrder = $this->machineByteOrder = WKBTools::getMachineByteOrder();
    }

    /**
     * @param integer $byteOrder The byte order, one of the BIG_ENDIAN or LITTLE_ENDIAN constants.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the byte order is invalid.
     */
    public function setByteOrder($byteOrder)
    {
        WKBTools::checkByteOrder($byteOrder);
        $this->byteOrder = $byteOrder;
    }

    /**
     * @param \Brick\Geo\Geometry $geometry
     *
     * @return string
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function write(Geometry $geometry)
    {
        if ($geometry instanceof Point) {
            return $this->writePoint($geometry);
        }
        if ($geometry instanceof LineString) {
            return $this->writeLineString($geometry);
        }
        if ($geometry instanceof Polygon) {
            return $this->writePolygon($geometry);
        }
        if ($geometry instanceof MultiPoint) {
            return $this->writeMultiPoint($geometry);
        }
        if ($geometry instanceof MultiLineString) {
            return $this->writeMultiLineString($geometry);
        }
        if ($geometry instanceof MultiPolygon) {
            return $this->writeMultiPolygon($geometry);
        }
        if ($geometry instanceof GeometryCollection) {
            return $this->writeGeometryCollection($geometry);
        }
        if ($geometry instanceof PolyhedralSurface) {
            return $this->writePolyhedralSurface($geometry);
        }
        if ($geometry instanceof TIN) {
            return $this->writeTIN($geometry);
        }
        if ($geometry instanceof Triangle) {
            return $this->writeTriangle($geometry);
        }

        throw GeometryException::unsupportedGeometryType($geometry);
    }

    /**
     * @return string
     */
    private function packByteOrder()
    {
        return pack('C', $this->byteOrder);
    }

    /**
     * @param integer $uint
     *
     * @return string
     */
    private function packUnsignedInteger($uint)
    {
        return pack($this->byteOrder === WKBTools::BIG_ENDIAN ? 'N' : 'V', $uint);
    }

    /**
     * @param float $double
     *
     * @return string
     */
    private function packDouble($double)
    {
        $binary = pack('d', $double);

        if ($this->byteOrder !== $this->machineByteOrder) {
            return strrev($binary);
        }

        return $binary;
    }

    /**
     * @param integer  $geometryType
     * @param Geometry $geometry
     *
     * @return string
     */
    private function packGeometryTypeZM($geometryType, Geometry $geometry)
    {
        if ($geometry->is3D()) {
            $geometryType += 1000;
        }

        if ($geometry->isMeasured()) {
            $geometryType += 2000;
        }

        return $this->packUnsignedInteger($geometryType);
    }

    /**
     * @param \Brick\Geo\Point $point
     *
     * @return string
     */
    private function packPoint(Point $point)
    {
        $binary = $this->packDouble($point->x()) . $this->packDouble($point->y());

        if (null !== $z = $point->z()) {
            $binary .= $this->packDouble($z);
        }
        if (null !== $m = $point->m()) {
            $binary .= $this->packDouble($m);
        }

        return $binary;
    }

    /**
     * @param \Brick\Geo\LineString $lineString
     *
     * @return string
     */
    private function packLineString(LineString $lineString)
    {
        $wkb = $this->packUnsignedInteger($lineString->count());

        foreach ($lineString as $point) {
            $wkb .= $this->packPoint($point);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\Point $point
     *
     * @return string
     */
    private function writePoint(Point $point)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packGeometryTypeZM(Geometry::POINT, $point);
        $wkb.= $this->packPoint($point);

        return $wkb;
    }

    /**
     * @param \Brick\Geo\LineString $lineString
     *
     * @return string
     */
    private function writeLineString(LineString $lineString)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packGeometryTypeZM(Geometry::LINESTRING, $lineString);
        $wkb.= $this->packLineString($lineString);

        return $wkb;
    }

    /**
     * @param \Brick\Geo\Polygon $polygon
     *
     * @return string
     */
    private function writePolygon(Polygon $polygon)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packGeometryTypeZM(Geometry::POLYGON, $polygon);
        $wkb.= $this->packUnsignedInteger($polygon->count());

        foreach ($polygon as $ring) {
            $wkb .= $this->packLineString($ring);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\Triangle $triangle
     *
     * @return string
     */
    private function writeTriangle(Triangle $triangle)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packGeometryTypeZM(Geometry::TRIANGLE, $triangle);
        $wkb.= $this->packUnsignedInteger($triangle->count());

        foreach ($triangle as $ring) {
            $wkb .= $this->packLineString($ring);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\MultiPoint $multiPoint
     *
     * @return string
     */
    private function writeMultiPoint(MultiPoint $multiPoint)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packGeometryTypeZM(Geometry::MULTIPOINT, $multiPoint);
        $wkb.= $this->packUnsignedInteger($multiPoint->count());

        foreach ($multiPoint as $point) {
            $wkb .= $this->writePoint($point);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\MultiLineString $multiLineString
     *
     * @return string
     */
    private function writeMultiLineString(MultiLineString $multiLineString)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packGeometryTypeZM(Geometry::MULTILINESTRING, $multiLineString);
        $wkb.= $this->packUnsignedInteger($multiLineString->count());

        foreach ($multiLineString as $lineString) {
            $wkb .= $this->writeLineString($lineString);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\MultiPolygon $multiPolygon
     *
     * @return string
     */
    private function writeMultiPolygon(MultiPolygon $multiPolygon)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packGeometryTypeZM(Geometry::MULTIPOLYGON, $multiPolygon);
        $wkb.= $this->packUnsignedInteger($multiPolygon->count());

        foreach ($multiPolygon as $polygon) {
            $wkb .= $this->writePolygon($polygon);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\GeometryCollection $collection
     *
     * @return string
     */
    private function writeGeometryCollection(GeometryCollection $collection)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packGeometryTypeZM(Geometry::GEOMETRYCOLLECTION, $collection);
        $wkb.= $this->packUnsignedInteger($collection->count());

        foreach ($collection as $geometry) {
            $wkb .= $this->write($geometry);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\PolyhedralSurface $surface
     *
     * @return string
     */
    private function writePolyhedralSurface(PolyhedralSurface $surface)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packGeometryTypeZM(Geometry::POLYHEDRALSURFACE, $surface);
        $wkb.= $this->packUnsignedInteger($surface->count());

        foreach ($surface as $polygon) {
            $wkb .= $this->writePolygon($polygon);
        }

        return $wkb;
    }

    /**
     * @param \Brick\Geo\TIN $tin
     *
     * @return string
     */
    private function writeTIN(TIN $tin)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packGeometryTypeZM(Geometry::TIN, $tin);
        $wkb.= $this->packUnsignedInteger($tin->count());

        foreach ($tin as $polygon) {
            $wkb .= $this->writePolygon($polygon);
        }

        return $wkb;
    }
}
