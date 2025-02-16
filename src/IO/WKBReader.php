<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Geometry;
use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Proxy;

/**
 * Builds geometries out of Well-Known Binary strings.
 */
final class WKBReader extends AbstractWKBReader
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

        return match ($geometryHeader->geometryType) {
            Geometry::POINT => new Proxy\PointProxy($wkb, true, $srid),
            Geometry::LINESTRING => new Proxy\LineStringProxy($wkb, true, $srid),
            Geometry::CIRCULARSTRING => new Proxy\CircularStringProxy($wkb, true, $srid),
            Geometry::COMPOUNDCURVE => new Proxy\CompoundCurveProxy($wkb, true, $srid),
            Geometry::POLYGON => new Proxy\PolygonProxy($wkb, true, $srid),
            Geometry::CURVEPOLYGON => new Proxy\CurvePolygonProxy($wkb, true, $srid),
            Geometry::MULTIPOINT => new Proxy\MultiPointProxy($wkb, true, $srid),
            Geometry::MULTILINESTRING => new Proxy\MultiLineStringProxy($wkb, true, $srid),
            Geometry::MULTIPOLYGON => new Proxy\MultiPolygonProxy($wkb, true, $srid),
            Geometry::GEOMETRYCOLLECTION => new Proxy\GeometryCollectionProxy($wkb, true, $srid),
            Geometry::POLYHEDRALSURFACE => new Proxy\PolyhedralSurfaceProxy($wkb, true, $srid),
            Geometry::TIN => new Proxy\TINProxy($wkb, true, $srid),
            Geometry::TRIANGLE => new Proxy\TriangleProxy($wkb, true, $srid),
            default => throw GeometryIOException::unsupportedWKBType($geometryHeader->geometryType),
        };
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
