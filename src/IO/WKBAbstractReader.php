<?php

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryException;
use Brick\Geo\Exception\GeometryParseException;
use Brick\Geo\Geometry;
use Brick\Geo\Point;
use Brick\Geo\LineString;
use Brick\Geo\CircularString;
use Brick\Geo\CompoundCurve;
use Brick\Geo\Polygon;
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
abstract class WKBAbstractReader
{
    /**
     * @param WKBBuffer $buffer       The WKB buffer.
     * @param integer   $geometryType A variable to store the geometry type.
     * @param boolean   $is3D         A variable to store whether the geometry has Z coordinates.
     * @param boolean   $isMeasured   A variable to store whether the geometry has M coordinates.
     * @param integer   $srid         A variable to store the SRID.
     *
     * @return void
     *
     * @throws GeometryException
     */
    abstract protected function readGeometryHeader(WKBBuffer $buffer, & $geometryType, & $is3D, & $isMeasured, & $srid);

    /**
     * @param WKBBuffer $buffer
     * @param integer   $srid
     *
     * @return Geometry
     *
     * @throws GeometryParseException
     */
    protected function readGeometry(WKBBuffer $buffer, $srid)
    {
        $buffer->readByteOrder();

        $this->readGeometryHeader($buffer, $geometryType, $is3D, $isMeasured, $srid);

        switch ($geometryType) {
            case Geometry::POINT:
                return $this->readPoint($buffer, $is3D, $isMeasured, $srid);

            case Geometry::LINESTRING:
                return $this->readLineString($buffer, $is3D, $isMeasured, $srid);

            case Geometry::CIRCULARSTRING:
                return $this->readCircularString($buffer, $is3D, $isMeasured, $srid);

            case Geometry::COMPOUNDCURVE:
                return $this->readCompoundCurve($buffer, $is3D, $isMeasured, $srid);

            case Geometry::POLYGON:
                return $this->readPolygon($buffer, $is3D, $isMeasured, $srid);

            case Geometry::MULTIPOINT:
                return $this->readMultiPoint($buffer, $is3D, $isMeasured, $srid);

            case Geometry::MULTILINESTRING:
                return $this->readMultiLineString($buffer, $is3D, $isMeasured, $srid);

            case Geometry::MULTIPOLYGON:
                return $this->readMultiPolygon($buffer, $is3D, $isMeasured, $srid);

            case Geometry::GEOMETRYCOLLECTION:
                return $this->readGeometryCollection($buffer, $is3D, $isMeasured, $srid);

            case Geometry::POLYHEDRALSURFACE:
                return $this->readPolyhedralSurface($buffer, $is3D, $isMeasured, $srid);

            case Geometry::TIN:
                return $this->readTIN($buffer, $is3D, $isMeasured, $srid);

            case Geometry::TRIANGLE:
                return $this->readTriangle($buffer, $is3D, $isMeasured, $srid);
        }

        throw GeometryParseException::unsupportedGeometryType($geometryType);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\Point
     */
    private function readPoint(WKBBuffer $buffer, $is3D, $isMeasured, $srid)
    {
        $count = 2 + ($is3D ? 1 : 0) + ($isMeasured ? 1 : 0);
        $values = $buffer->readDoubles($count);

        if ($is3D && $isMeasured) {
            return Point::xyzm($values[1], $values[2], $values[3], $values[4], $srid);
        }

        if ($is3D) {
            return Point::xyz($values[1], $values[2], $values[3], $srid);
        }

        if ($isMeasured) {
            return Point::xym($values[1], $values[2], $values[3], $srid);
        }

        return Point::xy($values[1], $values[2], $srid);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\LineString
     */
    private function readLineString(WKBBuffer $buffer, $is3D, $isMeasured, $srid)
    {
        $numPoints = $buffer->readUnsignedLong();

        $points = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $points[] = $this->readPoint($buffer, $is3D, $isMeasured, $srid);
        }

        return LineString::create($points, $is3D, $isMeasured, $srid);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\LineString
     */
    private function readCircularString(WKBBuffer $buffer, $is3D, $isMeasured, $srid)
    {
        $numPoints = $buffer->readUnsignedLong();

        $points = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $points[] = $this->readPoint($buffer, $is3D, $isMeasured, $srid);
        }

        return CircularString::create($points, $is3D, $isMeasured, $srid);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\CompoundCurve
     */
    private function readCompoundCurve(WKBBuffer $buffer, $is3D, $isMeasured, $srid)
    {
        $numCurves = $buffer->readUnsignedLong();
        $curves = [];

        for ($i = 0; $i < $numCurves; $i++) {
            $curves[] = $this->readGeometry($buffer, $srid);
        }

        return CompoundCurve::create($curves, $is3D, $isMeasured, $srid);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\Polygon
     */
    private function readPolygon(WKBBuffer $buffer, $is3D, $isMeasured, $srid)
    {
        $numRings = $buffer->readUnsignedLong();

        $rings = [];

        for ($i = 0; $i < $numRings; $i++) {
            $rings[] = $this->readLineString($buffer, $is3D, $isMeasured, $srid);
        }

        return Polygon::create($rings, $is3D, $isMeasured, $srid);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\MultiPoint
     */
    private function readMultiPoint(WKBBuffer $buffer, $is3D, $isMeasured, $srid)
    {
        $numPoints = $buffer->readUnsignedLong();
        $points = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $points[] = $this->readGeometry($buffer, $srid);
        }

        return MultiPoint::create($points, $is3D, $isMeasured, $srid);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\MultiLineString
     */
    private function readMultiLineString(WKBBuffer $buffer, $is3D, $isMeasured, $srid)
    {
        $numLineStrings = $buffer->readUnsignedLong();
        $lineStrings = [];

        for ($i = 0; $i < $numLineStrings; $i++) {
            $lineStrings[] = $this->readGeometry($buffer, $srid);
        }

        return MultiLineString::create($lineStrings, $is3D, $isMeasured, $srid);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\MultiPolygon
     */
    private function readMultiPolygon(WKBBuffer $buffer, $is3D, $isMeasured, $srid)
    {
        $numPolygons = $buffer->readUnsignedLong();
        $polygons = [];

        for ($i = 0; $i < $numPolygons; $i++) {
            $polygons[] = $this->readGeometry($buffer, $srid);
        }

        return MultiPolygon::create($polygons, $is3D, $isMeasured, $srid);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\GeometryCollection
     */
    private function readGeometryCollection(WKBBuffer $buffer, $is3D, $isMeasured, $srid)
    {
        $numGeometries = $buffer->readUnsignedLong();
        $geometries = [];

        for ($i = 0; $i < $numGeometries; $i++) {
            $geometries[] = $this->readGeometry($buffer, $srid);
        }

        return GeometryCollection::create($geometries, $is3D, $isMeasured, $srid);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\PolyhedralSurface
     */
    private function readPolyhedralSurface(WKBBuffer $buffer, $is3D, $isMeasured, $srid)
    {
        $numPatches = $buffer->readUnsignedLong();
        $patches = [];

        for ($i = 0; $i < $numPatches; $i++) {
            $patches[] = $this->readGeometry($buffer, $srid);
        }

        return PolyhedralSurface::create($patches, $is3D, $isMeasured, $srid);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\TIN
     */
    private function readTIN(WKBBuffer $buffer, $is3D, $isMeasured, $srid)
    {
        $numPatches = $buffer->readUnsignedLong();
        $patches = [];

        for ($i = 0; $i < $numPatches; $i++) {
            $patches[] = $this->readGeometry($buffer, $srid);
        }

        return TIN::create($patches, $is3D, $isMeasured, $srid);
    }

    /**
     * @param WKBBuffer $buffer
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\Triangle
     */
    private function readTriangle(WKBBuffer $buffer, $is3D, $isMeasured, $srid)
    {
        $numRings = $buffer->readUnsignedLong();

        $rings = [];

        for ($i = 0; $i < $numRings; $i++) {
            $rings[] = $this->readLineString($buffer, $is3D, $isMeasured, $srid);
        }

        return Triangle::create($rings, $is3D, $isMeasured, $srid);
    }
}
