<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;
use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Proxy;

/**
 * Builds geometries out of Well-Known Binary strings.
 */
class WKBReader extends AbstractWKBReader
{
    /**
     * @param string $wkb  The WKB to read.
     * @param int    $srid The optional SRID of the geometry.
     *
     * @throws GeometryIOException
     */
    public function read(string $wkb, int $srid = 0) : Geometry
    {
        $buffer = new WKBBuffer($wkb);
        $geometry = $this->readGeometry($buffer, $srid);

        if (! $buffer->isEndOfStream()) {
            throw GeometryIOException::invalidWKB('unexpected data at end of stream');
        }

        return $geometry;
    }

    /**
     * Introspects the given WKB and returns a proxy of the corresponding class.
     *
     * This prevents having to fully hydrate the underlying geometry object graph, while still returning an instance
     * of the correct geometry class.
     *
     * @return Geometry&Proxy\ProxyInterface
     *
     * @throws GeometryIOException
     */
    public function readAsProxy(string $wkb, int $srid = 0) : Geometry
    {
        $buffer = new WKBBuffer($wkb);
        $buffer->readByteOrder();
        $geometryHeader = $this->readGeometryHeader($buffer);

        switch ($geometryHeader->geometryType) {
            case Geometry::POINT:
                return new Proxy\PointProxy($wkb, true, $srid);

            case Geometry::LINESTRING:
                return new Proxy\LineStringProxy($wkb, true, $srid);

            case Geometry::CIRCULARSTRING:
                return new Proxy\CircularStringProxy($wkb, true, $srid);

            case Geometry::COMPOUNDCURVE:
                return new Proxy\CompoundCurveProxy($wkb, true, $srid);

            case Geometry::POLYGON:
                return new Proxy\PolygonProxy($wkb, true, $srid);

            case Geometry::CURVEPOLYGON:
                return new Proxy\CurvePolygonProxy($wkb, true, $srid);

            case Geometry::MULTIPOINT:
                return new Proxy\MultiPointProxy($wkb, true, $srid);

            case Geometry::MULTILINESTRING:
                return new Proxy\MultiLineStringProxy($wkb, true, $srid);

            case Geometry::MULTIPOLYGON:
                return new Proxy\MultiPolygonProxy($wkb, true, $srid);

            case Geometry::GEOMETRYCOLLECTION:
                return new Proxy\GeometryCollectionProxy($wkb, true, $srid);

            case Geometry::POLYHEDRALSURFACE:
                return new Proxy\PolyhedralSurfaceProxy($wkb, true, $srid);

            case Geometry::TIN:
                return new Proxy\TINProxy($wkb, true, $srid);

            case Geometry::TRIANGLE:
                return new Proxy\TriangleProxy($wkb, true, $srid);
        }

        throw GeometryIOException::unsupportedWKBType($geometryHeader->geometryType);
    }

    protected function readGeometryHeader(WKBBuffer $buffer) : WKBGeometryHeader
    {
        $wkbType = $buffer->readUnsignedLong();

        if ($wkbType < 0 || $wkbType >= 4000) {
            throw GeometryIOException::unsupportedWKBType($wkbType);
        }

        $geometryType = $wkbType % 1000;
        $dimension = ($wkbType - $geometryType) / 1000;

        $hasZ = ($dimension === 1 || $dimension === 3);
        $hasM = ($dimension === 2 || $dimension === 3);

        return new WKBGeometryHeader($geometryType, $hasZ, $hasM);
    }
}
