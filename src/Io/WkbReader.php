<?php

declare(strict_types=1);

namespace Brick\Geo\Io;

use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\Geometry;
use Brick\Geo\Io\Internal\AbstractWkbReader;
use Brick\Geo\Io\Internal\WkbBuffer;
use Brick\Geo\Io\Internal\WkbGeometryHeader;
use Brick\Geo\Proxy;
use Override;

/**
 * Builds geometries out of Well-Known Binary strings.
 */
final class WkbReader extends AbstractWkbReader
{
    /**
     * @param string $wkb  The WKB to read.
     * @param int    $srid The optional SRID of the geometry.
     *
     * @throws GeometryIoException
     */
    public function read(string $wkb, int $srid = 0): Geometry
    {
        $buffer = new WkbBuffer($wkb);
        $geometry = $this->readGeometry($buffer, $srid);

        if (! $buffer->isEndOfStream()) {
            throw GeometryIoException::invalidWkb('unexpected data at end of stream');
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
     * @throws GeometryIoException
     */
    public function readAsProxy(string $wkb, int $srid = 0): Geometry
    {
        $buffer = new WkbBuffer($wkb);
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
            Geometry::TIN => new Proxy\TinProxy($wkb, true, $srid),
            Geometry::TRIANGLE => new Proxy\TriangleProxy($wkb, true, $srid),
            default => throw GeometryIoException::unsupportedWkbType($geometryHeader->geometryType),
        };
    }

    #[Override]
    protected function readGeometryHeader(WkbBuffer $buffer): WkbGeometryHeader
    {
        $wkbType = $buffer->readUnsignedLong();

        if ($wkbType < 0 || $wkbType >= 4000) {
            throw GeometryIoException::unsupportedWkbType($wkbType);
        }

        $geometryType = $wkbType % 1000;
        $dimension = ($wkbType - $geometryType) / 1000;

        $hasZ = ($dimension === 1 || $dimension === 3);
        $hasM = ($dimension === 2 || $dimension === 3);

        return new WkbGeometryHeader($geometryType, $hasZ, $hasM);
    }
}
