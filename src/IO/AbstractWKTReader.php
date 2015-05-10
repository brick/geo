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
use Brick\Geo\Exception\GeometryIOException;

/**
 * Base class for WKTReader and EWKTReader.
 */
abstract class AbstractWKTReader
{
    /**
     * @param WKTParser $parser
     * @param integer   $srid
     *
     * @return Geometry
     *
     * @throws GeometryIOException
     */
    protected function readGeometry(WKTParser $parser, $srid)
    {
        $geometryType = $parser->getNextWord();
        $word = $parser->getOptionalNextWord();

        $hasZ = false;
        $hasM = false;
        $isEmpty    = false;

        if ($word !== null) {
            if ($word === 'Z') {
                $hasZ = true;
            } elseif ($word === 'M') {
                $hasM = true;
            } elseif ($word === 'ZM') {
                $hasZ = true;
                $hasM = true;
            } elseif ($word === 'EMPTY') {
                $isEmpty = true;
            } else {
                throw new GeometryIOException('Unexpected word in WKT: ' . $word);
            }

            if (! $isEmpty) {
                $word = $parser->getOptionalNextWord();

                if ($word === 'EMPTY') {
                    $isEmpty = true;
                } elseif ($word !== null) {
                    throw new GeometryIOException('Unexpected word in WKT: ' . $word);
                }
            }
        }

        $cs = new CoordinateSystem($hasZ, $hasM, $srid);

        switch ($geometryType) {
            case 'POINT':
                if ($isEmpty) {
                    return new Point($cs);
                }

                return $this->readPointText($parser, $cs);

            case 'LINESTRING':
                if ($isEmpty) {
                    return new LineString($cs);
                }

                return $this->readLineStringText($parser, $cs);

            case 'CIRCULARSTRING':
                if ($isEmpty) {
                    return new CircularString($cs);
                }

                return $this->readCircularStringText($parser, $cs);

            case 'COMPOUNDCURVE':
                if ($isEmpty) {
                    return new CompoundCurve($cs);
                }

                return $this->readCompoundCurveText($parser, $cs);

            case 'POLYGON':
                if ($isEmpty) {
                    return new Polygon($cs);
                }

                return $this->readPolygonText($parser, $cs);

            case 'CURVEPOLYGON':
                if ($isEmpty) {
                    return new CurvePolygon($cs);
                }

                return $this->readCurvePolygonText($parser, $cs);

            case 'MULTIPOINT':
                if ($isEmpty) {
                    return new MultiPoint($cs);
                }

                return $this->readMultiPointText($parser, $cs);

            case 'MULTILINESTRING':
                if ($isEmpty) {
                    return new MultiLineString($cs);
                }

                return $this->readMultiLineStringText($parser, $cs);

            case 'MULTIPOLYGON':
                if ($isEmpty) {
                    return new MultiPolygon($cs);
                }

                return $this->readMultiPolygonText($parser, $cs);

            case 'GEOMETRYCOLLECTION':
                if ($isEmpty) {
                    return new GeometryCollection($cs);
                }

                return $this->readGeometryCollectionText($parser, $cs);

            case 'POLYHEDRALSURFACE':
                if ($isEmpty) {
                    return new PolyhedralSurface($cs);
                }

                return $this->readPolyhedralSurfaceText($parser, $cs);

            case 'TIN':
                if ($isEmpty) {
                    return new TIN($cs);
                }

                return $this->readTINText($parser, $cs);

            case 'TRIANGLE':
                if ($isEmpty) {
                    return new Triangle($cs);
                }

            return $this->readTriangleText($parser, $cs);
        }

        throw new GeometryIOException('Unknown geometry type: ' . $geometryType);
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

        return new Point($cs, ...$coords);
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

        return new LineString($cs, ...$points);
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

        return new CircularString($cs, ...$points);
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

        return new CompoundCurve($cs, ...$curves);
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

        return new MultiPoint($cs, ...$points);
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

        return new Polygon($cs, ...$rings);
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

        return new CurvePolygon($cs, ...$curves);
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

        return new Triangle($cs, ...$rings);
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
        $lineStrings = $this->readMultiLineString($parser, $cs);

        return new MultiLineString($cs, ...$lineStrings);
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

        return new MultiPolygon($cs, ...$polygons);
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

        return new GeometryCollection($cs, ...$geometries);
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

        return new PolyhedralSurface($cs, ...$patches);
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

        return new TIN($cs, ...$patches);
    }
}
