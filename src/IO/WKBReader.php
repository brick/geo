<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\CircularString;
use Brick\Geo\CompoundCurve;
use Brick\Geo\CurvePolygon;
use Brick\Geo\Geometry;
use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\GeometryCollection;
use Brick\Geo\LineString;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiPolygon;
use Brick\Geo\Point;
use Brick\Geo\Polygon;
use Brick\Geo\PolyhedralSurface;
use Brick\Geo\Proxy\ProxyFactory;
use Brick\Geo\TIN;
use Brick\Geo\Triangle;

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
     * @throws GeometryIOException
     */
    public function readAsProxy(string $wkb, int $srid = 0) : Geometry
    {
        $buffer = new WKBBuffer($wkb);
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
            Geometry::TIN => TIN::class,
            Geometry::TRIANGLE => Triangle::class,
            default => throw GeometryIOException::unsupportedWKBType($geometryType),
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
