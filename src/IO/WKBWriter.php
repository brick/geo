<?php

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryException;
use Brick\Geo\Geometry;
use Brick\Geo\Point;
use Brick\Geo\LineString;
use Brick\Geo\CircularString;
use Brick\Geo\CompoundCurve;
use Brick\Geo\Polygon;
use Brick\Geo\CurvePolygon;
use Brick\Geo\Triangle;
use Brick\Geo\GeometryCollection;
use Brick\Geo\PolyhedralSurface;

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
     * @param Geometry $geometry
     *
     * @return string
     *
     * @throws GeometryException
     */
    public function write(Geometry $geometry)
    {
        return $this->doWrite($geometry, true);
    }

    /**
     * @param Geometry $geometry The geometry to write.
     * @param boolean  $outer    False if the geometry is nested in a collection, true otherwise.
     *
     * @return string
     *
     * @throws GeometryException
     */
    protected function doWrite(Geometry $geometry, $outer)
    {
        if ($geometry instanceof Point) {
            return $this->writePoint($geometry, $outer);
        }
        if ($geometry instanceof LineString) {
            return $this->writeLineString($geometry, $outer);
        }
        if ($geometry instanceof CircularString) {
            return $this->writeCircularString($geometry, $outer);
        }
        if ($geometry instanceof CompoundCurve) {
            return $this->writeComposedGeometry($geometry, $outer);
        }
        if ($geometry instanceof Triangle) {
            return $this->writeTriangle($geometry, $outer);
        }
        if ($geometry instanceof Polygon) {
            return $this->writePolygon($geometry, $outer);
        }
        if ($geometry instanceof CurvePolygon) {
            return $this->writeComposedGeometry($geometry, $outer);
        }
        if ($geometry instanceof GeometryCollection) {
            return $this->writeComposedGeometry($geometry, $outer);
        }
        if ($geometry instanceof PolyhedralSurface) {
            return $this->writeComposedGeometry($geometry, $outer);
        }

        throw GeometryException::unsupportedGeometryType($geometry->geometryType());
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
    protected function packUnsignedInteger($uint)
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
     * @param Geometry $geometry
     * @param boolean  $outer
     *
     * @return string
     */
    protected function packHeader(Geometry $geometry, $outer)
    {
        $geometryType = $geometry->geometryTypeBinary();

        $cs = $geometry->coordinateSystem();

        if ($cs->hasZ()) {
            $geometryType += 1000;
        }

        if ($cs->hasM()) {
            $geometryType += 2000;
        }

        return $this->packUnsignedInteger($geometryType);
    }

    /**
     * @param \Brick\Geo\Point $point
     *
     * @return string
     *
     * @throws GeometryException
     */
    private function packPoint(Point $point)
    {
        if ($point->isEmpty()) {
            throw new GeometryException('Empty points have no WKB representation.');
        }

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
     * @param \Brick\Geo\CircularString $circularString
     *
     * @return string
     */
    private function packCircularString(CircularString $circularString)
    {
        $wkb = $this->packUnsignedInteger($circularString->count());

        foreach ($circularString as $point) {
            $wkb .= $this->packPoint($point);
        }

        return $wkb;
    }

    /**
     * @param Point   $point
     * @param boolean $outer
     *
     * @return string
     */
    private function writePoint(Point $point, $outer)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packHeader($point, $outer);
        $wkb.= $this->packPoint($point);

        return $wkb;
    }

    /**
     * @param LineString $lineString
     * @param boolean    $outer
     *
     * @return string
     */
    private function writeLineString(LineString $lineString, $outer)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packHeader($lineString, $outer);
        $wkb.= $this->packLineString($lineString);

        return $wkb;
    }

    /**
     * @param CircularString $circularString
     * @param boolean        $outer
     *
     * @return string
     */
    private function writeCircularString(CircularString $circularString, $outer)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packHeader($circularString, $outer);
        $wkb.= $this->packCircularString($circularString);

        return $wkb;
    }

    /**
     * @param Polygon $polygon
     * @param boolean $outer
     *
     * @return string
     */
    private function writePolygon(Polygon $polygon, $outer)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packHeader($polygon, $outer);
        $wkb.= $this->packUnsignedInteger($polygon->count());

        foreach ($polygon as $ring) {
            $wkb .= $this->packLineString($ring);
        }

        return $wkb;
    }

    /**
     * @param Triangle $triangle
     * @param boolean $outer
     *
     * @return string
     */
    private function writeTriangle(Triangle $triangle, $outer)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packHeader($triangle, $outer);
        $wkb.= $this->packUnsignedInteger($triangle->count());

        foreach ($triangle as $ring) {
            $wkb .= $this->packLineString($ring);
        }

        return $wkb;
    }

    /**
     * @param Geometry $collection
     * @param boolean  $outer
     *
     * @return string
     */
    private function writeComposedGeometry(Geometry $collection, $outer)
    {
        $wkb = $this->packByteOrder();
        $wkb.= $this->packHeader($collection, $outer);
        $wkb.= $this->packUnsignedInteger($collection->count());

        foreach ($collection as $geometry) {
            $wkb .= $this->doWrite($geometry, false);
        }

        return $wkb;
    }
}
