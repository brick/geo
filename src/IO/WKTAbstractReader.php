<?php

namespace Brick\Geo\IO;

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
use Brick\Geo\CoordinateSystem;
use Brick\Geo\Exception\GeometryException;

/**
 * Base class for WKTReader and EWKTReader.
 */
abstract class WKTAbstractReader
{
    /**
     * @param WKTParser $parser
     * @param integer   $srid
     *
     * @return Geometry
     *
     * @throws GeometryException
     */
    protected function readGeometry(WKTParser $parser, $srid)
    {
        $geometryType = $parser->getNextWord();
        $word = $parser->getOptionalNextWord();

        $is3D       = false;
        $isMeasured = false;
        $isEmpty    = false;

        if ($word !== null) {
            if ($word === 'Z') {
                $is3D = true;
            } elseif ($word === 'M') {
                $isMeasured = true;
            } elseif ($word === 'ZM') {
                $is3D       = true;
                $isMeasured = true;
            } elseif ($word === 'EMPTY') {
                $isEmpty = true;
            } else {
                throw new GeometryException('Unexpected word in WKT: ' . $word);
            }

            if (! $isEmpty) {
                $word = $parser->getOptionalNextWord();

                if ($word === 'EMPTY') {
                    $isEmpty = true;
                } elseif ($word !== null) {
                    throw new GeometryException('Unexpected word in WKT: ' . $word);
                }
            }
        }

        $cs = CoordinateSystem::create($is3D, $isMeasured, $srid);

        switch ($geometryType) {
            case 'POINT':
                if ($isEmpty) {
                    return Point::create([], $cs);
                }

                return $this->readPointText($parser, $cs);

            case 'LINESTRING':
                if ($isEmpty) {
                    return LineString::create([], $cs);
                }

                return $this->readLineStringText($parser, $cs);

            case 'CIRCULARSTRING':
                if ($isEmpty) {
                    return CircularString::create([], $cs);
                }

                return $this->readCircularStringText($parser, $cs);

            case 'COMPOUNDCURVE':
                if ($isEmpty) {
                    return CompoundCurve::create([], $cs);
                }

                return $this->readCompoundCurveText($parser, $cs);

            case 'POLYGON':
                if ($isEmpty) {
                    return Polygon::create([], $cs);
                }

                return $this->readPolygonText($parser, $cs);

            case 'CURVEPOLYGON':
                if ($isEmpty) {
                    return CurvePolygon::create([], $cs);
                }

                return $this->readCurvePolygonText($parser, $cs);

            case 'MULTIPOINT':
                if ($isEmpty) {
                    return MultiPoint::create([], $cs);
                }

                return $this->readMultiPointText($parser, $cs);

            case 'MULTILINESTRING':
                if ($isEmpty) {
                    return MultiLineString::create([], $cs);
                }

                return $this->readMultiLineStringText($parser, $cs);

            case 'MULTIPOLYGON':
                if ($isEmpty) {
                    return MultiPolygon::create([], $cs);
                }

                return $this->readMultiPolygonText($parser, $cs);

            case 'GEOMETRYCOLLECTION':
                if ($isEmpty) {
                    return GeometryCollection::create([], $cs);
                }

                return $this->readGeometryCollectionText($parser, $cs);

            case 'POLYHEDRALSURFACE':
                if ($isEmpty) {
                    return PolyhedralSurface::create([], $cs);
                }

                return $this->readPolyhedralSurfaceText($parser, $cs);

            case 'TIN':
                if ($isEmpty) {
                    return TIN::create([], $cs);
                }

                return $this->readTINText($parser, $cs);

            case 'TRIANGLE':
                if ($isEmpty) {
                    return Triangle::create([], $cs);
                }

            return $this->readTriangleText($parser, $cs);
        }

        throw new GeometryException('Unknown geometry type: ' . $geometryType);
    }

    /**
     * x y
     *
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return Point
     */
    private function readPoint(WKTParser $parser, CoordinateSystem $cs)
    {
        $dim = $cs->coordinateDimension();
        $coords = [];

        for ($i = 0; $i < $dim; $i++) {
            $coords[] = $parser->getNextNumber();
        }

        return Point::create($coords, $cs);
    }

    /**
     * (x y)
     *
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return Point
     */
    private function readPointText(WKTParser $parser, CoordinateSystem $cs)
    {
        $parser->matchOpener();
        $point = $this->readPoint($parser, $cs);
        $parser->matchCloser();

        return $point;
    }

    /**
     * (x y, ...)
     *
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return Point[]
     */
    private function readMultiPoint(WKTParser $parser, CoordinateSystem $cs)
    {
        $parser->matchOpener();
        $points = [];

        do {
            $points[] = $this->readPoint($parser, $cs);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return $points;
    }

    /**
     * (x y, ...)
     *
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return LineString
     */
    private function readLineStringText(WKTParser $parser, CoordinateSystem $cs)
    {
        $points = $this->readMultiPoint($parser, $cs);

        return LineString::create($points, $cs);
    }

    /**
     * (x y, ...)
     *
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return CircularString
     */
    private function readCircularStringText(WKTParser $parser, CoordinateSystem $cs)
    {
        $points = $this->readMultiPoint($parser, $cs);

        return CircularString::create($points, $cs);
    }

    /**
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return CompoundCurve
     */
    private function readCompoundCurveText(WKTParser $parser, CoordinateSystem $cs)
    {
        $parser->matchOpener();
        $curves = [];

        do {
            if ($parser->isNextOpenerOrWord()) {
                $curves[] = $this->readLineStringText($parser, $cs);
            } else {
                $curves[] = $this->readGeometry($parser, $cs->SRID());
            }

            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return CompoundCurve::create($curves, $cs);
    }

    /**
     * (x y, ...)
     *
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return MultiPoint
     */
    private function readMultiPointText(WKTParser $parser, CoordinateSystem $cs)
    {
        $points = $this->readMultiPoint($parser, $cs);

        return MultiPoint::create($points, $cs);
    }

    /**
     * ((x y, ...), ...)
     *
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return LineString[]
     */
    private function readMultiLineString(WKTParser $parser, CoordinateSystem $cs)
    {
        $parser->matchOpener();
        $lineStrings = [];

        do {
            $lineStrings[] = $this->readLineStringText($parser, $cs);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return $lineStrings;
    }

    /**
     * ((x y, ...), ...)
     *
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return Polygon
     */
    private function readPolygonText(WKTParser $parser, CoordinateSystem $cs)
    {
        $rings = $this->readMultiLineString($parser, $cs);

        return Polygon::create($rings, $cs);
    }

    /**
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return CurvePolygon
     */
    private function readCurvePolygonText(WKTParser $parser, CoordinateSystem $cs)
    {
        $parser->matchOpener();
        $curves = [];

        do {
            if ($parser->isNextOpenerOrWord()) {
                $curves[] = $this->readLineStringText($parser, $cs);
            } else {
                $curves[] = $this->readGeometry($parser, $cs->SRID());
            }

            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return CurvePolygon::create($curves, $cs);
    }

    /**
     * ((x y, ...), ...)
     *
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return Triangle
     */
    private function readTriangleText(WKTParser $parser, CoordinateSystem $cs)
    {
        $rings = $this->readMultiLineString($parser, $cs);

        return Triangle::create($rings, $cs);
    }

    /**
     * ((x y, ...), ...)
     *
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return MultiLineString
     */
    private function readMultiLineStringText(WKTParser $parser, CoordinateSystem $cs)
    {
        $rings = $this->readMultiLineString($parser, $cs);

        return MultiLineString::create($rings, $cs);
    }

    /**
     * (((x y, ...), ...), ...)
     *
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return MultiPolygon
     */
    private function readMultiPolygonText(WKTParser $parser, CoordinateSystem $cs)
    {
        $parser->matchOpener();
        $polygons = [];

        do {
            $polygons[] = $this->readPolygonText($parser, $cs);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return MultiPolygon::create($polygons, $cs);
    }

    /**
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return GeometryCollection
     */
    private function readGeometryCollectionText(WKTParser $parser, CoordinateSystem $cs)
    {
        $parser->matchOpener();
        $geometries = [];

        do {
            $geometries[] = $this->readGeometry($parser, $cs->SRID());
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return GeometryCollection::create($geometries, $cs);
    }

    /**
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return PolyhedralSurface
     */
    private function readPolyhedralSurfaceText(WKTParser $parser, CoordinateSystem $cs)
    {
        $parser->matchOpener();
        $patches = [];

        do {
            $patches[] = $this->readPolygonText($parser, $cs);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return PolyhedralSurface::create($patches, $cs);
    }

    /**
     * @param WKTParser        $parser
     * @param CoordinateSystem $cs
     *
     * @return TIN
     */
    private function readTINText(WKTParser $parser, CoordinateSystem $cs)
    {
        $parser->matchOpener();
        $patches = [];

        do {
            $patches[] = $this->readTriangleText($parser, $cs);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return TIN::create($patches, $cs);
    }
}
