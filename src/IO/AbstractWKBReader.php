<?php

namespace Brick\Geo\IO;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;
use Brick\Geo\Point;
use Brick\Geo\LineString;
use Brick\Geo\CircularString;
use Brick\Geo\CompoundCurve;
use Brick\Geo\Polygon;
use Brick\Geo\CurvePolygon;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPolygon;
use Brick\Geo\GeometryCollection;
use Brick\Geo\PolyhedralSurface;
use Brick\Geo\TIN;
use Brick\Geo\Triangle;

/**
 * Base class for WKBReader and EWKBReader.
 */
abstract class AbstractWKBReader
{
    /**
     * @param WKBBuffer $buffer       The WKB buffer.
     * @param int       $geometryType A variable to store the geometry type.
     * @param bool      $hasZ         A variable to store whether the geometry has Z coordinates.
     * @param bool      $hasM         A variable to store whether the geometry has M coordinates.
     * @param int       $srid         A variable to store the SRID.
     *
     * @return void
     *
     * @throws GeometryIOException
     */
    abstract protected function readGeometryHeader(WKBBuffer $buffer, & $geometryType, & $hasZ, & $hasM, & $srid);

    /**
     * @param WKBBuffer $buffer
     * @param int       $srid
     *
     * @return Geometry
     *
     * @throws GeometryIOException
     */
    protected function readGeometry(WKBBuffer $buffer, $srid)
    {
        $buffer->readByteOrder();

        $this->readGeometryHeader($buffer, $geometryType, $hasZ, $hasM, $srid);

        $cs = new CoordinateSystem($hasZ, $hasM, $srid);

        switch ($geometryType) {
            case Geometry::POINT:
                return $this->readPoint($buffer, $cs);

            case Geometry::LINESTRING:
                return $this->readLineString($buffer, $cs);

            case Geometry::CIRCULARSTRING:
                return $this->readCircularString($buffer, $cs);

            case Geometry::COMPOUNDCURVE:
                return $this->readCompoundCurve($buffer, $cs);

            case Geometry::POLYGON:
                return $this->readPolygon($buffer, $cs);

            case Geometry::CURVEPOLYGON:
                return $this->readCurvePolygon($buffer, $cs);

            case Geometry::MULTIPOINT:
                return $this->readMultiPoint($buffer, $cs);

            case Geometry::MULTILINESTRING:
                return $this->readMultiLineString($buffer, $cs);

            case Geometry::MULTIPOLYGON:
                return $this->readMultiPolygon($buffer, $cs);

            case Geometry::GEOMETRYCOLLECTION:
                return $this->readGeometryCollection($buffer, $cs);

            case Geometry::POLYHEDRALSURFACE:
                return $this->readPolyhedralSurface($buffer, $cs);

            case Geometry::TIN:
                return $this->readTIN($buffer, $cs);

            case Geometry::TRIANGLE:
                return $this->readTriangle($buffer, $cs);
        }

        throw GeometryIOException::unsupportedGeometryType($geometryType);
    }

    /**
     * @param WKBBuffer        $buffer
     * @param CoordinateSystem $cs
     *
     * @return Point
     */
    private function readPoint(WKBBuffer $buffer, CoordinateSystem $cs)
    {
        $coords = $buffer->readDoubles($cs->coordinateDimension());

        return new Point($cs, ...$coords);
    }

    /**
     * @param WKBBuffer        $buffer
     * @param CoordinateSystem $cs
     *
     * @return LineString
     */
    private function readLineString(WKBBuffer $buffer, CoordinateSystem $cs)
    {
        $numPoints = $buffer->readUnsignedLong();

        $points = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $points[] = $this->readPoint($buffer, $cs);
        }

        return new LineString($cs, ...$points);
    }

    /**
     * @param WKBBuffer        $buffer
     * @param CoordinateSystem $cs
     *
     * @return CircularString
     */
    private function readCircularString(WKBBuffer $buffer, CoordinateSystem $cs)
    {
        $numPoints = $buffer->readUnsignedLong();

        $points = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $points[] = $this->readPoint($buffer, $cs);
        }

        return new CircularString($cs, ...$points);
    }

    /**
     * @param WKBBuffer        $buffer
     * @param CoordinateSystem $cs
     *
     * @return CompoundCurve
     */
    private function readCompoundCurve(WKBBuffer $buffer, CoordinateSystem $cs)
    {
        $numCurves = $buffer->readUnsignedLong();
        $curves = [];

        for ($i = 0; $i < $numCurves; $i++) {
            $curves[] = $this->readGeometry($buffer, $cs->SRID());
        }

        return new CompoundCurve($cs, ...$curves);
    }

    /**
     * @param WKBBuffer        $buffer
     * @param CoordinateSystem $cs
     *
     * @return Polygon
     */
    private function readPolygon(WKBBuffer $buffer, CoordinateSystem $cs)
    {
        $numRings = $buffer->readUnsignedLong();

        $rings = [];

        for ($i = 0; $i < $numRings; $i++) {
            $rings[] = $this->readLineString($buffer, $cs);
        }

        return new Polygon($cs, ...$rings);
    }

    /**
     * @param WKBBuffer        $buffer
     * @param CoordinateSystem $cs
     *
     * @return CurvePolygon
     */
    private function readCurvePolygon(WKBBuffer $buffer, CoordinateSystem $cs)
    {
        $numRings = $buffer->readUnsignedLong();

        $rings = [];

        for ($i = 0; $i < $numRings; $i++) {
            $rings[] = $this->readGeometry($buffer, $cs->SRID());
        }

        return new CurvePolygon($cs, ...$rings);
    }

    /**
     * @param WKBBuffer        $buffer
     * @param CoordinateSystem $cs
     *
     * @return MultiPoint
     */
    private function readMultiPoint(WKBBuffer $buffer, CoordinateSystem $cs)
    {
        $numPoints = $buffer->readUnsignedLong();
        $points = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $points[] = $this->readGeometry($buffer, $cs->SRID());
        }

        return new MultiPoint($cs, ...$points);
    }

    /**
     * @param WKBBuffer        $buffer
     * @param CoordinateSystem $cs
     *
     * @return MultiLineString
     */
    private function readMultiLineString(WKBBuffer $buffer, CoordinateSystem $cs)
    {
        $numLineStrings = $buffer->readUnsignedLong();
        $lineStrings = [];

        for ($i = 0; $i < $numLineStrings; $i++) {
            $lineStrings[] = $this->readGeometry($buffer, $cs->SRID());
        }

        return new MultiLineString($cs, ...$lineStrings);
    }

    /**
     * @param WKBBuffer        $buffer
     * @param CoordinateSystem $cs
     *
     * @return MultiPolygon
     */
    private function readMultiPolygon(WKBBuffer $buffer, CoordinateSystem $cs)
    {
        $numPolygons = $buffer->readUnsignedLong();
        $polygons = [];

        for ($i = 0; $i < $numPolygons; $i++) {
            $polygons[] = $this->readGeometry($buffer, $cs->SRID());
        }

        return new MultiPolygon($cs, ...$polygons);
    }

    /**
     * @param WKBBuffer        $buffer
     * @param CoordinateSystem $cs
     *
     * @return GeometryCollection
     */
    private function readGeometryCollection(WKBBuffer $buffer, CoordinateSystem $cs)
    {
        $numGeometries = $buffer->readUnsignedLong();
        $geometries = [];

        for ($i = 0; $i < $numGeometries; $i++) {
            $geometries[] = $this->readGeometry($buffer, $cs->SRID());
        }

        return new GeometryCollection($cs, ...$geometries);
    }

    /**
     * @param WKBBuffer        $buffer
     * @param CoordinateSystem $cs
     *
     * @return PolyhedralSurface
     */
    private function readPolyhedralSurface(WKBBuffer $buffer, CoordinateSystem $cs)
    {
        $numPatches = $buffer->readUnsignedLong();
        $patches = [];

        for ($i = 0; $i < $numPatches; $i++) {
            $patches[] = $this->readGeometry($buffer, $cs->SRID());
        }

        return new PolyhedralSurface($cs, ...$patches);
    }

    /**
     * @param WKBBuffer        $buffer
     * @param CoordinateSystem $cs
     *
     * @return TIN
     */
    private function readTIN(WKBBuffer $buffer, CoordinateSystem $cs)
    {
        $numPatches = $buffer->readUnsignedLong();
        $patches = [];

        for ($i = 0; $i < $numPatches; $i++) {
            $patches[] = $this->readGeometry($buffer, $cs->SRID());
        }

        return new TIN($cs, ...$patches);
    }

    /**
     * @param WKBBuffer        $buffer
     * @param CoordinateSystem $cs
     *
     * @return Triangle
     */
    private function readTriangle(WKBBuffer $buffer, CoordinateSystem $cs)
    {
        $numRings = $buffer->readUnsignedLong();

        $rings = [];

        for ($i = 0; $i < $numRings; $i++) {
            $rings[] = $this->readLineString($buffer, $cs);
        }

        return new Triangle($cs, ...$rings);
    }
}
