<?php

declare(strict_types=1);

namespace Brick\Geo\IO\Internal;

use Brick\Geo\CircularString;
use Brick\Geo\CompoundCurve;
use Brick\Geo\CoordinateSystem;
use Brick\Geo\Curve;
use Brick\Geo\CurvePolygon;
use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;
use Brick\Geo\GeometryCollection;
use Brick\Geo\LineString;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiPolygon;
use Brick\Geo\Point;
use Brick\Geo\Polygon;
use Brick\Geo\PolyhedralSurface;
use Brick\Geo\Tin;
use Brick\Geo\Triangle;

/**
 * Base class for WKBReader and EWKBReader.
 *
 * @internal
 */
abstract class AbstractWKBReader
{
    /**
     * @throws GeometryIOException
     */
    abstract protected function readGeometryHeader(WKBBuffer $buffer) : WKBGeometryHeader;

    /**
     * @throws GeometryIOException
     */
    protected function readGeometry(WKBBuffer $buffer, int $srid) : Geometry
    {
        $buffer->readByteOrder();

        $geometryHeader = $this->readGeometryHeader($buffer);

        $cs = new CoordinateSystem(
            $geometryHeader->hasZ,
            $geometryHeader->hasM,
            $geometryHeader->srid ?? $srid
        );

        return match ($geometryHeader->geometryType) {
            Geometry::POINT => $this->readPoint($buffer, $cs),
            Geometry::LINESTRING => $this->readLineString($buffer, $cs),
            Geometry::CIRCULARSTRING => $this->readCircularString($buffer, $cs),
            Geometry::COMPOUNDCURVE => $this->readCompoundCurve($buffer, $cs),
            Geometry::POLYGON => $this->readPolygon($buffer, $cs),
            Geometry::CURVEPOLYGON => $this->readCurvePolygon($buffer, $cs),
            Geometry::MULTIPOINT => $this->readMultiPoint($buffer, $cs),
            Geometry::MULTILINESTRING => $this->readMultiLineString($buffer, $cs),
            Geometry::MULTIPOLYGON => $this->readMultiPolygon($buffer, $cs),
            Geometry::GEOMETRYCOLLECTION => $this->readGeometryCollection($buffer, $cs),
            Geometry::POLYHEDRALSURFACE => $this->readPolyhedralSurface($buffer, $cs),
            Geometry::TIN => $this->readTin($buffer, $cs),
            Geometry::TRIANGLE => $this->readTriangle($buffer, $cs),
            default => throw GeometryIOException::unsupportedWKBType($geometryHeader->geometryType),
        };
    }

    private function readPoint(WKBBuffer $buffer, CoordinateSystem $cs) : Point
    {
        $coords = $buffer->readDoubles($cs->coordinateDimension());

        return new Point($cs, ...$coords);
    }

    private function readLineString(WKBBuffer $buffer, CoordinateSystem $cs) : LineString
    {
        $numPoints = $buffer->readUnsignedLong();

        $points = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $points[] = $this->readPoint($buffer, $cs);
        }

        return new LineString($cs, ...$points);
    }

    private function readCircularString(WKBBuffer $buffer, CoordinateSystem $cs) : CircularString
    {
        $numPoints = $buffer->readUnsignedLong();

        $points = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $points[] = $this->readPoint($buffer, $cs);
        }

        return new CircularString($cs, ...$points);
    }

    /**
     * @throws GeometryIOException
     */
    private function readCompoundCurve(WKBBuffer $buffer, CoordinateSystem $cs) : CompoundCurve
    {
        $numCurves = $buffer->readUnsignedLong();
        $curves = [];

        for ($i = 0; $i < $numCurves; $i++) {
            $curve = $this->readGeometry($buffer, $cs->SRID());

            if (! $curve instanceof LineString && ! $curve instanceof CircularString) {
                throw new GeometryIOException('Expected LineString|CircularString, got ' . $curve->geometryType());
            }

            $curves[] = $curve;
        }

        return new CompoundCurve($cs, ...$curves);
    }

    private function readPolygon(WKBBuffer $buffer, CoordinateSystem $cs) : Polygon
    {
        $numRings = $buffer->readUnsignedLong();

        $rings = [];

        for ($i = 0; $i < $numRings; $i++) {
            $rings[] = $this->readLineString($buffer, $cs);
        }

        return new Polygon($cs, ...$rings);
    }

    /**
     * @throws GeometryIOException
     */
    private function readCurvePolygon(WKBBuffer $buffer, CoordinateSystem $cs) : CurvePolygon
    {
        $numRings = $buffer->readUnsignedLong();

        $rings = [];

        for ($i = 0; $i < $numRings; $i++) {
            $ring = $this->readGeometry($buffer, $cs->SRID());

            if (! $ring instanceof Curve) {
                throw new GeometryIOException('Expected Curve, got ' . $ring->geometryType());
            }

            $rings[] = $ring;
        }

        return new CurvePolygon($cs, ...$rings);
    }

    /**
     * @throws GeometryIOException
     */
    private function readMultiPoint(WKBBuffer $buffer, CoordinateSystem $cs) : MultiPoint
    {
        $numPoints = $buffer->readUnsignedLong();
        $points = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $point = $this->readGeometry($buffer, $cs->SRID());

            if (! $point instanceof Point) {
                throw new GeometryIOException('Expected Point, got ' . $point->geometryType());
            }

            $points[] = $point;
        }

        return new MultiPoint($cs, ...$points);
    }

    /**
     * @throws GeometryIOException
     */
    private function readMultiLineString(WKBBuffer $buffer, CoordinateSystem $cs) : MultiLineString
    {
        $numLineStrings = $buffer->readUnsignedLong();
        $lineStrings = [];

        for ($i = 0; $i < $numLineStrings; $i++) {
            $lineString = $this->readGeometry($buffer, $cs->SRID());

            if (! $lineString instanceof LineString) {
                throw new GeometryIOException('Expected LineString, got ' . $lineString->geometryType());
            }

            $lineStrings[] = $lineString;
        }

        return new MultiLineString($cs, ...$lineStrings);
    }

    /**
     * @throws GeometryIOException
     */
    private function readMultiPolygon(WKBBuffer $buffer, CoordinateSystem $cs) : MultiPolygon
    {
        $numPolygons = $buffer->readUnsignedLong();
        $polygons = [];

        for ($i = 0; $i < $numPolygons; $i++) {
            $polygon = $this->readGeometry($buffer, $cs->SRID());

            if (! $polygon instanceof Polygon) {
                throw new GeometryIOException('Expected Polygon, got ' . $polygon->geometryType());
            }

            $polygons[] = $polygon;
        }

        return new MultiPolygon($cs, ...$polygons);
    }

    private function readGeometryCollection(WKBBuffer $buffer, CoordinateSystem $cs) : GeometryCollection
    {
        $numGeometries = $buffer->readUnsignedLong();
        $geometries = [];

        for ($i = 0; $i < $numGeometries; $i++) {
            $geometries[] = $this->readGeometry($buffer, $cs->SRID());
        }

        return new GeometryCollection($cs, ...$geometries);
    }

    /**
     * @throws GeometryIOException
     */
    private function readPolyhedralSurface(WKBBuffer $buffer, CoordinateSystem $cs) : PolyhedralSurface
    {
        $numPatches = $buffer->readUnsignedLong();
        $patches = [];

        for ($i = 0; $i < $numPatches; $i++) {
            $patch = $this->readGeometry($buffer, $cs->SRID());

            if (! $patch instanceof Polygon) {
                throw new GeometryIOException('Expected Polygon, got ' . $patch->geometryType());
            }

            $patches[] = $patch;
        }

        return new PolyhedralSurface($cs, ...$patches);
    }

    /**
     * @throws GeometryIOException
     */
    private function readTin(WKBBuffer $buffer, CoordinateSystem $cs) : Tin
    {
        $numPatches = $buffer->readUnsignedLong();
        $patches = [];

        for ($i = 0; $i < $numPatches; $i++) {
            $patch = $this->readGeometry($buffer, $cs->SRID());

            if (! $patch instanceof Triangle) {
                throw new GeometryIOException('Expected Triangle, got ' . $patch->geometryType());
            }

            $patches[] = $patch;
        }

        return new Tin($cs, ...$patches);
    }

    private function readTriangle(WKBBuffer $buffer, CoordinateSystem $cs) : Triangle
    {
        $numRings = $buffer->readUnsignedLong();

        $rings = [];

        for ($i = 0; $i < $numRings; $i++) {
            $rings[] = $this->readLineString($buffer, $cs);
        }

        return new Triangle($cs, ...$rings);
    }
}
