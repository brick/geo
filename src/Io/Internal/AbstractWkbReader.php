<?php

declare(strict_types=1);

namespace Brick\Geo\Io\Internal;

use Brick\Geo\CircularString;
use Brick\Geo\CompoundCurve;
use Brick\Geo\CoordinateSystem;
use Brick\Geo\Curve;
use Brick\Geo\CurvePolygon;
use Brick\Geo\Exception\GeometryIoException;
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
 * Base class for WkbReader and EwkbReader.
 *
 * @internal
 */
abstract class AbstractWkbReader
{
    /**
     * @throws GeometryIoException
     */
    abstract protected function readGeometryHeader(WkbBuffer $buffer) : WkbGeometryHeader;

    /**
     * @throws GeometryIoException
     */
    protected function readGeometry(WkbBuffer $buffer, int $srid) : Geometry
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
            default => throw GeometryIoException::unsupportedWkbType($geometryHeader->geometryType),
        };
    }

    private function readPoint(WkbBuffer $buffer, CoordinateSystem $cs) : Point
    {
        $coords = $buffer->readDoubles($cs->coordinateDimension());

        return new Point($cs, ...$coords);
    }

    private function readLineString(WkbBuffer $buffer, CoordinateSystem $cs) : LineString
    {
        $numPoints = $buffer->readUnsignedLong();

        $points = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $points[] = $this->readPoint($buffer, $cs);
        }

        return new LineString($cs, ...$points);
    }

    private function readCircularString(WkbBuffer $buffer, CoordinateSystem $cs) : CircularString
    {
        $numPoints = $buffer->readUnsignedLong();

        $points = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $points[] = $this->readPoint($buffer, $cs);
        }

        return new CircularString($cs, ...$points);
    }

    /**
     * @throws GeometryIoException
     */
    private function readCompoundCurve(WkbBuffer $buffer, CoordinateSystem $cs) : CompoundCurve
    {
        $numCurves = $buffer->readUnsignedLong();
        $curves = [];

        for ($i = 0; $i < $numCurves; $i++) {
            $curve = $this->readGeometry($buffer, $cs->srid());

            if (! $curve instanceof LineString && ! $curve instanceof CircularString) {
                throw new GeometryIoException('Expected LineString|CircularString, got ' . $curve->geometryType());
            }

            $curves[] = $curve;
        }

        return new CompoundCurve($cs, ...$curves);
    }

    private function readPolygon(WkbBuffer $buffer, CoordinateSystem $cs) : Polygon
    {
        $numRings = $buffer->readUnsignedLong();

        $rings = [];

        for ($i = 0; $i < $numRings; $i++) {
            $rings[] = $this->readLineString($buffer, $cs);
        }

        return new Polygon($cs, ...$rings);
    }

    /**
     * @throws GeometryIoException
     */
    private function readCurvePolygon(WkbBuffer $buffer, CoordinateSystem $cs) : CurvePolygon
    {
        $numRings = $buffer->readUnsignedLong();

        $rings = [];

        for ($i = 0; $i < $numRings; $i++) {
            $ring = $this->readGeometry($buffer, $cs->srid());

            if (! $ring instanceof Curve) {
                throw new GeometryIoException('Expected Curve, got ' . $ring->geometryType());
            }

            $rings[] = $ring;
        }

        return new CurvePolygon($cs, ...$rings);
    }

    /**
     * @throws GeometryIoException
     */
    private function readMultiPoint(WkbBuffer $buffer, CoordinateSystem $cs) : MultiPoint
    {
        $numPoints = $buffer->readUnsignedLong();
        $points = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $point = $this->readGeometry($buffer, $cs->srid());

            if (! $point instanceof Point) {
                throw new GeometryIoException('Expected Point, got ' . $point->geometryType());
            }

            $points[] = $point;
        }

        return new MultiPoint($cs, ...$points);
    }

    /**
     * @throws GeometryIoException
     */
    private function readMultiLineString(WkbBuffer $buffer, CoordinateSystem $cs) : MultiLineString
    {
        $numLineStrings = $buffer->readUnsignedLong();
        $lineStrings = [];

        for ($i = 0; $i < $numLineStrings; $i++) {
            $lineString = $this->readGeometry($buffer, $cs->srid());

            if (! $lineString instanceof LineString) {
                throw new GeometryIoException('Expected LineString, got ' . $lineString->geometryType());
            }

            $lineStrings[] = $lineString;
        }

        return new MultiLineString($cs, ...$lineStrings);
    }

    /**
     * @throws GeometryIoException
     */
    private function readMultiPolygon(WkbBuffer $buffer, CoordinateSystem $cs) : MultiPolygon
    {
        $numPolygons = $buffer->readUnsignedLong();
        $polygons = [];

        for ($i = 0; $i < $numPolygons; $i++) {
            $polygon = $this->readGeometry($buffer, $cs->srid());

            if (! $polygon instanceof Polygon) {
                throw new GeometryIoException('Expected Polygon, got ' . $polygon->geometryType());
            }

            $polygons[] = $polygon;
        }

        return new MultiPolygon($cs, ...$polygons);
    }

    private function readGeometryCollection(WkbBuffer $buffer, CoordinateSystem $cs) : GeometryCollection
    {
        $numGeometries = $buffer->readUnsignedLong();
        $geometries = [];

        for ($i = 0; $i < $numGeometries; $i++) {
            $geometries[] = $this->readGeometry($buffer, $cs->srid());
        }

        return new GeometryCollection($cs, ...$geometries);
    }

    /**
     * @throws GeometryIoException
     */
    private function readPolyhedralSurface(WkbBuffer $buffer, CoordinateSystem $cs) : PolyhedralSurface
    {
        $numPatches = $buffer->readUnsignedLong();
        $patches = [];

        for ($i = 0; $i < $numPatches; $i++) {
            $patch = $this->readGeometry($buffer, $cs->srid());

            if (! $patch instanceof Polygon) {
                throw new GeometryIoException('Expected Polygon, got ' . $patch->geometryType());
            }

            $patches[] = $patch;
        }

        return new PolyhedralSurface($cs, ...$patches);
    }

    /**
     * @throws GeometryIoException
     */
    private function readTin(WkbBuffer $buffer, CoordinateSystem $cs) : Tin
    {
        $numPatches = $buffer->readUnsignedLong();
        $patches = [];

        for ($i = 0; $i < $numPatches; $i++) {
            $patch = $this->readGeometry($buffer, $cs->srid());

            if (! $patch instanceof Triangle) {
                throw new GeometryIoException('Expected Triangle, got ' . $patch->geometryType());
            }

            $patches[] = $patch;
        }

        return new Tin($cs, ...$patches);
    }

    private function readTriangle(WkbBuffer $buffer, CoordinateSystem $cs) : Triangle
    {
        $numRings = $buffer->readUnsignedLong();

        $rings = [];

        for ($i = 0; $i < $numRings; $i++) {
            $rings[] = $this->readLineString($buffer, $cs);
        }

        return new Triangle($cs, ...$rings);
    }
}
