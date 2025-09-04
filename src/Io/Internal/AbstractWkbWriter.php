<?php

declare(strict_types=1);

namespace Brick\Geo\Io\Internal;

use Brick\Geo\CircularString;
use Brick\Geo\CompoundCurve;
use Brick\Geo\Curve;
use Brick\Geo\CurvePolygon;
use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\Geometry;
use Brick\Geo\GeometryCollection;
use Brick\Geo\LineString;
use Brick\Geo\Point;
use Brick\Geo\Polygon;
use Brick\Geo\PolyhedralSurface;

use function pack;
use function sprintf;
use function strrev;

/**
 * Base class for WkbWriter and EwkbWriter.
 *
 * @internal
 */
abstract class AbstractWkbWriter
{
    private WkbByteOrder $byteOrder;

    private WkbByteOrder $machineByteOrder;

    /**
     * @throws GeometryIoException
     */
    public function __construct()
    {
        $this->byteOrder = $this->machineByteOrder = WkbTools::getMachineByteOrder();
    }

    public function setByteOrder(WkbByteOrder $byteOrder): void
    {
        $this->byteOrder = $byteOrder;
    }

    /**
     * @param Geometry $geometry The geometry to export as WKB.
     *
     * @return string The WKB representation of the given geometry.
     *
     * @throws GeometryIoException If the given geometry cannot be exported as WKB.
     */
    public function write(Geometry $geometry): string
    {
        return $this->doWrite($geometry, true);
    }

    /**
     * @param Geometry $geometry The geometry export as WKB write.
     * @param bool     $outer    False if the geometry is nested in another geometry, true otherwise.
     *
     * @return string The WKB representation of the given geometry.
     *
     * @throws GeometryIoException If the given geometry cannot be exported as WKT.
     */
    protected function doWrite(Geometry $geometry, bool $outer): string
    {
        if ($geometry instanceof Point) {
            return $this->writePoint($geometry, $outer);
        }

        if ($geometry instanceof LineString) {
            return $this->writeCurve($geometry, $outer);
        }

        if ($geometry instanceof CircularString) {
            return $this->writeCurve($geometry, $outer);
        }

        if ($geometry instanceof Polygon) {
            return $this->writePolygon($geometry, $outer);
        }

        if ($geometry instanceof CompoundCurve) {
            return $this->writeComposedGeometry($geometry, $outer);
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

        throw GeometryIoException::unsupportedGeometryType($geometry->geometryType());
    }

    protected function packUnsignedInteger(int $uint): string
    {
        return pack(match ($this->byteOrder) {
            WkbByteOrder::BigEndian => 'N',
            WkbByteOrder::LittleEndian => 'V'
        }, $uint);
    }

    abstract protected function packHeader(Geometry $geometry, bool $outer): string;

    private function packByteOrder(): string
    {
        return pack('C', $this->byteOrder->value);
    }

    private function packDouble(float $double): string
    {
        $binary = pack('d', $double);

        if ($this->byteOrder !== $this->machineByteOrder) {
            return strrev($binary);
        }

        return $binary;
    }

    /**
     * @throws GeometryIoException
     */
    private function packPoint(Point $point): string
    {
        if ($point->isEmpty()) {
            throw new GeometryIoException('Empty points have no WKB representation.');
        }

        /** @psalm-suppress PossiblyNullArgument */
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
     * @throws GeometryIoException
     */
    private function packCurve(Curve $curve): string
    {
        if (! $curve instanceof LineString && ! $curve instanceof CircularString) {
            // CompoundCurve is not a list of Points, not sure if WKB supports it!
            // For now, let's just not support it ourselves.
            throw new GeometryIoException(sprintf('Writing a %s as WKB is not supported.', $curve->geometryType()));
        }

        $wkb = $this->packUnsignedInteger($curve->count());

        foreach ($curve as $point) {
            $wkb .= $this->packPoint($point);
        }

        return $wkb;
    }

    private function writePoint(Point $point, bool $outer): string
    {
        $wkb = $this->packByteOrder();
        $wkb .= $this->packHeader($point, $outer);
        $wkb .= $this->packPoint($point);

        return $wkb;
    }

    private function writeCurve(Curve $curve, bool $outer): string
    {
        $wkb = $this->packByteOrder();
        $wkb .= $this->packHeader($curve, $outer);
        $wkb .= $this->packCurve($curve);

        return $wkb;
    }

    private function writePolygon(Polygon $polygon, bool $outer): string
    {
        $wkb = $this->packByteOrder();
        $wkb .= $this->packHeader($polygon, $outer);
        $wkb .= $this->packUnsignedInteger($polygon->count());

        foreach ($polygon as $ring) {
            $wkb .= $this->packCurve($ring);
        }

        return $wkb;
    }

    private function writeComposedGeometry(CompoundCurve|CurvePolygon|GeometryCollection|PolyhedralSurface $collection, bool $outer): string
    {
        $wkb = $this->packByteOrder();
        $wkb .= $this->packHeader($collection, $outer);
        $wkb .= $this->packUnsignedInteger($collection->count());

        foreach ($collection as $geometry) {
            $wkb .= $this->doWrite($geometry, false);
        }

        return $wkb;
    }
}
