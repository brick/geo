<?php

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryException;
use Brick\Geo\Geometry;
use Brick\Geo\Point;
use Brick\Geo\LineString;
use Brick\Geo\Polygon;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPolygon;
use Brick\Geo\GeometryCollection;
use Brick\Geo\PolyhedralSurface;
use Brick\Geo\TIN;

/**
 * Builds geometries out of Well-Known Binary strings.
 */
class WKBReader
{
    /**
     * @param string  $wkb  The WKB to read.
     * @param integer $srid The optional SRID of the geometry.
     *
     * @return \Brick\Geo\Geometry
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function read($wkb, $srid = 0)
    {
        $buffer = new WKBBuffer($wkb);
        $geometry = $this->readGeometry($buffer, $srid);

        if (! $buffer->isEndOfStream()) {
            throw GeometryException::invalidWkb('unexpected data at end of stream');
        }

        return $geometry;
    }

    /**
     * @param WKBBuffer $buffer
     * @param integer   $srid
     *
     * @return \Brick\Geo\Geometry
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    protected function readGeometry(WKBBuffer $buffer, $srid)
    {
        $buffer->readByteOrder();
        $wkbType = $buffer->readUnsignedLong();

        $geometryType = $wkbType % 1000;
        $dimension = ($wkbType - $geometryType) / 1000;

        if ($dimension < 0 || $dimension > 3) {
            throw GeometryException::unsupportedWkbType($wkbType);
        }

        $is3D = ($dimension === 1 || $dimension === 3);
        $isMeasured = ($dimension === 2 || $dimension === 3);

        switch ($geometryType) {
            case Geometry::POINT:
                return $this->readPoint($buffer, $is3D, $isMeasured, $srid);
            case Geometry::LINESTRING:
                return $this->readLineString($buffer, $is3D, $isMeasured, $srid);
            case Geometry::POLYGON:
            case Geometry::TRIANGLE:
                return $this->readPolygon($buffer, $is3D, $isMeasured, $srid);
            case Geometry::MULTIPOINT:
                return $this->readMultiPoint($buffer, $srid);
            case Geometry::MULTILINESTRING:
                return $this->readMultiLineString($buffer, $srid);
            case Geometry::MULTIPOLYGON:
                return $this->readMultiPolygon($buffer, $srid);
            case Geometry::GEOMETRYCOLLECTION:
                return $this->readGeometryCollection($buffer, $srid);
            case Geometry::POLYHEDRALSURFACE:
                return $this->readPolyhedralSurface($buffer, $srid);
            case Geometry::TIN:
                return $this->readTIN($buffer, $srid);
        }

        throw GeometryException::unsupportedWkbType($wkbType);
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

        $x = $values[1];
        $y = $values[2];

        $z = $is3D ? $values[3] : null;
        $m = $isMeasured ? $values[$is3D ? 4 : 3] : null;

        return Point::factory($x, $y, $z, $m, $srid);
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

        for ($i=0; $i<$numPoints; $i++) {
            $points[] = $this->readPoint($buffer, $is3D, $isMeasured, $srid);
        }

        return LineString::factory($points);
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

        for ($i=0; $i<$numRings; $i++) {
            $rings[] = $this->readLineString($buffer, $is3D, $isMeasured, $srid);
        }

        return Polygon::factory($rings);
    }

    /**
     * @param WKBBuffer $buffer
     * @param integer   $srid
     *
     * @return \Brick\Geo\MultiPoint
     */
    private function readMultiPoint(WKBBuffer $buffer, $srid)
    {
        $numPoints = $buffer->readUnsignedLong();
        $points = [];

        for ($i=0; $i<$numPoints; $i++) {
            $points[] = $this->readGeometry($buffer, $srid);
        }

        return MultiPoint::factory($points);
    }

    /**
     * @param WKBBuffer $buffer
     * @param integer   $srid
     *
     * @return \Brick\Geo\MultiLineString
     */
    private function readMultiLineString(WKBBuffer $buffer, $srid)
    {
        $numLineStrings = $buffer->readUnsignedLong();
        $lineStrings = [];

        for ($i=0; $i<$numLineStrings; $i++) {
            $lineStrings[] = $this->readGeometry($buffer, $srid);
        }

        return MultiLineString::factory($lineStrings);
    }

    /**
     * @param WKBBuffer $buffer
     * @param integer   $srid
     *
     * @return \Brick\Geo\MultiPolygon
     */
    private function readMultiPolygon(WKBBuffer $buffer, $srid)
    {
        $numPolygons = $buffer->readUnsignedLong();
        $polygons = [];

        for ($i=0; $i<$numPolygons; $i++) {
            $polygons[] = $this->readGeometry($buffer, $srid);
        }

        return MultiPolygon::factory($polygons);
    }

    /**
     * @param WKBBuffer $buffer
     * @param integer   $srid
     *
     * @return \Brick\Geo\GeometryCollection
     */
    private function readGeometryCollection(WKBBuffer $buffer, $srid)
    {
        $numGeometries = $buffer->readUnsignedLong();
        $geometries = [];

        for ($i=0; $i<$numGeometries; $i++) {
            $geometries[] = $this->readGeometry($buffer, $srid);
        }

        return GeometryCollection::factory($geometries);
    }

    /**
     * @param WKBBuffer $buffer
     * @param integer   $srid
     *
     * @return \Brick\Geo\PolyhedralSurface
     */
    private function readPolyhedralSurface(WKBBuffer $buffer, $srid)
    {
        $numPolygons = $buffer->readUnsignedLong();
        $polygons = [];

        for ($i=0; $i<$numPolygons; $i++) {
            $polygons[] = $this->readGeometry($buffer, $srid);
        }

        return PolyhedralSurface::factory($polygons);
    }

    /**
     * @param WKBBuffer $buffer
     * @param integer   $srid
     *
     * @return \Brick\Geo\TIN
     */
    private function readTIN(WKBBuffer $buffer, $srid)
    {
        $numPolygons = $buffer->readUnsignedLong();
        $polygons = [];

        for ($i=0; $i<$numPolygons; $i++) {
            $polygons[] = $this->readGeometry($buffer, $srid);
        }

        return TIN::factory($polygons);
    }
}
