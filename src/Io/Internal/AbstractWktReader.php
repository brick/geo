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
 * Base class for WktReader and EwktReader.
 *
 * @internal
 */
abstract readonly class AbstractWktReader
{
    /**
     * @throws GeometryIoException
     */
    protected function readGeometry(WktParser $parser, int $srid) : Geometry
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
                throw new GeometryIoException('Unexpected word in WKT: ' . $word);
            }

            if (! $isEmpty) {
                $word = $parser->getOptionalNextWord();

                if ($word === 'EMPTY') {
                    $isEmpty = true;
                } elseif ($word !== null) {
                    throw new GeometryIoException('Unexpected word in WKT: ' . $word);
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
                    return new Tin($cs);
                }

                return $this->readTinText($parser, $cs);

            case 'TRIANGLE':
                if ($isEmpty) {
                    return new Triangle($cs);
                }

            return $this->readTriangleText($parser, $cs);
        }

        throw new GeometryIoException('Unknown geometry type: ' . $geometryType);
    }

    /**
     * x y
     */
    private function readPoint(WktParser $parser, CoordinateSystem $cs) : Point
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
     */
    private function readPointText(WktParser $parser, CoordinateSystem $cs) : Point
    {
        $parser->matchOpener();
        $point = $this->readPoint($parser, $cs);
        $parser->matchCloser();

        return $point;
    }

    /**
     * (x y, ...)
     * ((x, y), ...)
     *
     * @return list<Point>
     */
    private function readMultiPoint(WktParser $parser, CoordinateSystem $cs) : array
    {
        $parser->matchOpener();
        $points = [];

        do {
            $hasExtraParentheses = $parser->matchOptionalOpener();
            $points[] = $this->readPoint($parser, $cs);
            if ($hasExtraParentheses) {
                $parser->matchCloser();
            }

            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return $points;
    }

    /**
     * (x y, ...)
     */
    private function readLineStringText(WktParser $parser, CoordinateSystem $cs) : LineString
    {
        $points = $this->readMultiPoint($parser, $cs);

        return new LineString($cs, ...$points);
    }

    /**
     * (x y, ...)
     */
    private function readCircularStringText(WktParser $parser, CoordinateSystem $cs) : CircularString
    {
        $points = $this->readMultiPoint($parser, $cs);

        return new CircularString($cs, ...$points);
    }

    /**
     * @throws GeometryIoException
     */
    private function readCompoundCurveText(WktParser $parser, CoordinateSystem $cs) : CompoundCurve
    {
        $parser->matchOpener();
        $curves = [];

        do {
            if ($parser->isNextOpenerOrWord()) {
                $curves[] = $this->readLineStringText($parser, $cs);
            } else {
                $curve = $this->readGeometry($parser, $cs->srid());

                if (! $curve instanceof LineString && ! $curve instanceof CircularString) {
                    throw new GeometryIoException('Expected LineString|CircularString, got ' . $curve->geometryType());
                }

                $curves[] = $curve;
            }

            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return new CompoundCurve($cs, ...$curves);
    }

    /**
     * (x y, ...)
     */
    private function readMultiPointText(WktParser $parser, CoordinateSystem $cs) : MultiPoint
    {
        $points = $this->readMultiPoint($parser, $cs);

        return new MultiPoint($cs, ...$points);
    }

    /**
     * ((x y, ...), ...)
     *
     * @return list<LineString>
     */
    private function readMultiLineString(WktParser $parser, CoordinateSystem $cs) : array
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
     */
    private function readPolygonText(WktParser $parser, CoordinateSystem $cs) : Polygon
    {
        $rings = $this->readMultiLineString($parser, $cs);

        return new Polygon($cs, ...$rings);
    }

    /**
     * @throws GeometryIoException
     */
    private function readCurvePolygonText(WktParser $parser, CoordinateSystem $cs) : CurvePolygon
    {
        $parser->matchOpener();
        $curves = [];

        do {
            if ($parser->isNextOpenerOrWord()) {
                $curves[] = $this->readLineStringText($parser, $cs);
            } else {
                $curve = $this->readGeometry($parser, $cs->srid());

                if (! $curve instanceof Curve) {
                    throw new GeometryIoException('Expected Curve, got ' . $curve->geometryType());
                }

                $curves[] = $curve;
            }

            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return new CurvePolygon($cs, ...$curves);
    }

    /**
     * ((x y, ...), ...)
     */
    private function readTriangleText(WktParser $parser, CoordinateSystem $cs) : Triangle
    {
        $rings = $this->readMultiLineString($parser, $cs);

        return new Triangle($cs, ...$rings);
    }

    /**
     * ((x y, ...), ...)
     */
    private function readMultiLineStringText(WktParser $parser, CoordinateSystem $cs) : MultiLineString
    {
        $lineStrings = $this->readMultiLineString($parser, $cs);

        return new MultiLineString($cs, ...$lineStrings);
    }

    /**
     * (((x y, ...), ...), ...)
     */
    private function readMultiPolygonText(WktParser $parser, CoordinateSystem $cs) : MultiPolygon
    {
        $parser->matchOpener();
        $polygons = [];

        do {
            $polygons[] = $this->readPolygonText($parser, $cs);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return new MultiPolygon($cs, ...$polygons);
    }

    private function readGeometryCollectionText(WktParser $parser, CoordinateSystem $cs) : GeometryCollection
    {
        $parser->matchOpener();
        $geometries = [];

        do {
            $geometries[] = $this->readGeometry($parser, $cs->srid());
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return new GeometryCollection($cs, ...$geometries);
    }

    private function readPolyhedralSurfaceText(WktParser $parser, CoordinateSystem $cs) : PolyhedralSurface
    {
        $parser->matchOpener();
        $patches = [];

        do {
            $patches[] = $this->readPolygonText($parser, $cs);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return new PolyhedralSurface($cs, ...$patches);
    }

    private function readTinText(WktParser $parser, CoordinateSystem $cs) : Tin
    {
        $parser->matchOpener();
        $patches = [];

        do {
            $patches[] = $this->readTriangleText($parser, $cs);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return new Tin($cs, ...$patches);
    }
}
