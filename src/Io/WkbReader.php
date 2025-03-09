<?php

declare(strict_types=1);

namespace Brick\Geo\Io;

use Brick\Geo\CircularString;
use Brick\Geo\CompoundCurve;
use Brick\Geo\CurvePolygon;
use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\Geometry;
use Brick\Geo\Io\Internal\AbstractWkbReader;
use Brick\Geo\Io\Internal\WkbBuffer;
use Brick\Geo\Io\Internal\WkbGeometryHeader;
use Brick\Geo\GeometryCollection;
use Brick\Geo\LineString;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiPolygon;
use Brick\Geo\Point;
use Brick\Geo\Polygon;
use Brick\Geo\PolyhedralSurface;
use Brick\Geo\Proxy\ProxyFactory;
use Brick\Geo\Tin;
use Brick\Geo\Triangle;
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
    public function read(string $wkb, int $srid = 0) : Geometry
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
     * @throws GeometryIoException
     */
    public function readAsProxy(string $wkb, int $srid = 0) : Geometry
    {
        $buffer = new WkbBuffer($wkb);
        $buffer->readByteOrder();
        $geometryHeader = $this->readGeometryHeader($buffer);

        $geometryClass = $this->getGeometryClass($geometryHeader->geometryType);

        return ProxyFactory::createWkbProxy($geometryClass, $wkb, $srid);
    }

    /**
     * @return class-string<Geometry>
     *
     * @throws GeometryIOException
     */
    private function getGeometryClass(int $geometryType) : string
    {
        return match ($geometryType) {
            Geometry::POINT => Point::class,
            Geometry::LINESTRING => LineString::class,
            Geometry::CIRCULARSTRING => CircularString::class,
            Geometry::COMPOUNDCURVE => CompoundCurve::class,
            Geometry::POLYGON => Polygon::class,
            Geometry::CURVEPOLYGON => CurvePolygon::class,
            Geometry::MULTIPOINT => MultiPoint::class,
            Geometry::MULTILINESTRING => MultiLineString::class,
            Geometry::MULTIPOLYGON => MultiPolygon::class,
            Geometry::GEOMETRYCOLLECTION => GeometryCollection::class,
            Geometry::POLYHEDRALSURFACE => PolyhedralSurface::class,
            Geometry::TIN => Tin::class,
            Geometry::TRIANGLE => Triangle::class,
            default => throw GeometryIOException::unsupportedWkbType($geometryType),
        };
    }

    #[Override]
    protected function readGeometryHeader(WkbBuffer $buffer) : WkbGeometryHeader
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
