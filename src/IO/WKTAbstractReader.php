<?php

namespace Brick\Geo\IO;

use Brick\Geo\Point;
use Brick\Geo\LineString;
use Brick\Geo\CircularString;
use Brick\Geo\Polygon;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPolygon;
use Brick\Geo\GeometryCollection;
use Brick\Geo\PolyhedralSurface;
use Brick\Geo\TIN;
use Brick\Geo\Triangle;
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
     * @return \Brick\Geo\Geometry
     *
     * @throws \Brick\Geo\Exception\GeometryException
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

        switch ($geometryType) {
            case 'POINT':
                if ($isEmpty) {
                    return Point::pointEmpty($is3D, $isMeasured, $srid);
                }

                return $this->readPointText($parser, $is3D, $isMeasured, $srid);

            case 'LINESTRING':
                if ($isEmpty) {
                    return LineString::create([], $is3D, $isMeasured, $srid);
                }

                return $this->readLineStringText($parser, $is3D, $isMeasured, $srid);

            case 'CIRCULARSTRING':
                if ($isEmpty) {
                    return CircularString::create([], $is3D, $isMeasured, $srid);
                }

                return $this->readCircularStringText($parser, $is3D, $isMeasured, $srid);

            case 'POLYGON':
                if ($isEmpty) {
                    return Polygon::create([], $is3D, $isMeasured, $srid);
                }

                return $this->readPolygonText($parser, $is3D, $isMeasured, $srid);

            case 'MULTIPOINT':
                if ($isEmpty) {
                    return MultiPoint::create([], $is3D, $isMeasured, $srid);
                }

                return $this->readMultiPointText($parser, $is3D, $isMeasured, $srid);

            case 'MULTILINESTRING':
                if ($isEmpty) {
                    return MultiLineString::create([], $is3D, $isMeasured, $srid);
                }

                return $this->readMultiLineStringText($parser, $is3D, $isMeasured, $srid);

            case 'MULTIPOLYGON':
                if ($isEmpty) {
                    return MultiPolygon::create([], $is3D, $isMeasured, $srid);
                }

                return $this->readMultiPolygonText($parser, $is3D, $isMeasured, $srid);

            case 'GEOMETRYCOLLECTION':
                if ($isEmpty) {
                    return GeometryCollection::create([], $is3D, $isMeasured, $srid);
                }

                return $this->readGeometryCollectionText($parser, $is3D, $isMeasured, $srid);

            case 'POLYHEDRALSURFACE':
                if ($isEmpty) {
                    return PolyhedralSurface::create([], $is3D, $isMeasured, $srid);
                }

                return $this->readPolyhedralSurfaceText($parser, $is3D, $isMeasured, $srid);

            case 'TIN':
                if ($isEmpty) {
                    return TIN::create([], $is3D, $isMeasured, $srid);
                }

                return $this->readTINText($parser, $is3D, $isMeasured, $srid);

            case 'TRIANGLE':
                if ($isEmpty) {
                    return Triangle::create([], $is3D, $isMeasured, $srid);
                }

            return $this->readTriangleText($parser, $is3D, $isMeasured, $srid);
        }

        throw new GeometryException('Unknown geometry type: ' . $geometryType);
    }

    /**
     * x y
     *
     * @param WKTParser $parser
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\Point
     */
    private function readPoint(WKTParser $parser, $is3D, $isMeasured, $srid)
    {
        $coords = [];

        $coords[] = $parser->getNextNumber();
        $coords[] = $parser->getNextNumber();

        if ($is3D) {
            $coords[] = $parser->getNextNumber();
        }

        if ($isMeasured) {
            $coords[] = $parser->getNextNumber();
        }

        if ($is3D && $isMeasured) {
            return Point::xyzm($coords[0], $coords[1], $coords[2], $coords[3], $srid);
        }

        if ($is3D) {
            return Point::xyz($coords[0], $coords[1], $coords[2], $srid);
        }

        if ($isMeasured) {
            return Point::xym($coords[0], $coords[1], $coords[2], $srid);
        }

        return Point::xy($coords[0], $coords[1], $srid);
    }

    /**
     * (x y)
     *
     * @param WKTParser $parser
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\Point
     */
    private function readPointText(WKTParser $parser, $is3D, $isMeasured, $srid)
    {
        $parser->matchOpener();
        $point = $this->readPoint($parser, $is3D, $isMeasured, $srid);
        $parser->matchCloser();

        return $point;
    }

    /**
     * (x y, ...)
     *
     * @param WKTParser $parser
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\Point[]
     */
    private function readMultiPoint(WKTParser $parser, $is3D, $isMeasured, $srid)
    {
        $parser->matchOpener();
        $points = [];

        do {
            $points[] = $this->readPoint($parser, $is3D, $isMeasured, $srid);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return $points;
    }

    /**
     * (x y, ...)
     *
     * @param WKTParser $parser
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\LineString
     */
    private function readLineStringText(WKTParser $parser, $is3D, $isMeasured, $srid)
    {
        $points = $this->readMultiPoint($parser, $is3D, $isMeasured, $srid);

        return LineString::create($points, $is3D, $isMeasured, $srid);
    }

    /**
     * (x y, ...)
     *
     * @param WKTParser $parser
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\LineString
     */
    private function readCircularStringText(WKTParser $parser, $is3D, $isMeasured, $srid)
    {
        $points = $this->readMultiPoint($parser, $is3D, $isMeasured, $srid);

        return CircularString::create($points, $is3D, $isMeasured, $srid);
    }

    /**
     * (x y, ...)
     *
     * @param WKTParser $parser
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\MultiPoint
     */
    private function readMultiPointText(WKTParser $parser, $is3D, $isMeasured, $srid)
    {
        $points = $this->readMultiPoint($parser, $is3D, $isMeasured, $srid);

        return MultiPoint::factory($points);
    }

    /**
     * ((x y, ...), ...)
     *
     * @param WKTParser $parser
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\LineString[]
     */
    private function readMultiLineString(WKTParser $parser, $is3D, $isMeasured, $srid)
    {
        $parser->matchOpener();
        $lineStrings = [];

        do {
            $lineStrings[] = $this->readLineStringText($parser, $is3D, $isMeasured, $srid);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return $lineStrings;
    }

    /**
     * ((x y, ...), ...)
     *
     * @param WKTParser $parser
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\Polygon
     */
    private function readPolygonText(WKTParser $parser, $is3D, $isMeasured, $srid)
    {
        $rings = $this->readMultiLineString($parser, $is3D, $isMeasured, $srid);

        return Polygon::create($rings, $is3D, $isMeasured, $srid);
    }

    /**
     * ((x y, ...), ...)
     *
     * @param WKTParser $parser
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\Polygon
     */
    private function readTriangleText(WKTParser $parser, $is3D, $isMeasured, $srid)
    {
        $rings = $this->readMultiLineString($parser, $is3D, $isMeasured, $srid);

        return Triangle::create($rings, $is3D, $isMeasured, $srid);
    }

    /**
     * ((x y, ...), ...)
     *
     * @param WKTParser $parser
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\MultiLineString
     */
    private function readMultiLineStringText(WKTParser $parser, $is3D, $isMeasured, $srid)
    {
        $rings = $this->readMultiLineString($parser, $is3D, $isMeasured, $srid);

        return MultiLineString::factory($rings);
    }

    /**
     * (((x y, ...), ...), ...)
     *
     * @param WKTParser $parser
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\MultiPolygon
     */
    private function readMultiPolygonText(WKTParser $parser, $is3D, $isMeasured, $srid)
    {
        $parser->matchOpener();
        $polygons = [];

        do {
            $polygons[] = $this->readPolygonText($parser, $is3D, $isMeasured, $srid);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return MultiPolygon::factory($polygons);
    }

    /**
     * @param WKTParser $parser
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\GeometryCollection
     *
     * @throws GeometryException
     */
    private function readGeometryCollectionText(WKTParser $parser, $is3D, $isMeasured, $srid)
    {
        $parser->matchOpener();
        $geometries = [];

        do {
            $geometries[] = $this->readGeometry($parser, $srid);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return GeometryCollection::create($geometries, $is3D, $isMeasured, $srid);
    }

    /**
     * @param WKTParser $parser
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\PolyhedralSurface
     *
     * @throws GeometryException
     */
    private function readPolyhedralSurfaceText(WKTParser $parser, $is3D, $isMeasured, $srid)
    {
        $parser->matchOpener();
        $patches = [];

        do {
            $patches[] = $this->readPolygonText($parser, $is3D, $isMeasured, $srid);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return PolyhedralSurface::create($patches, $is3D, $isMeasured, $srid);
    }

    /**
     * @param WKTParser $parser
     * @param boolean   $is3D
     * @param boolean   $isMeasured
     * @param integer   $srid
     *
     * @return \Brick\Geo\TIN
     *
     * @throws GeometryException
     */
    private function readTINText(WKTParser $parser, $is3D, $isMeasured, $srid)
    {
        $parser->matchOpener();
        $patches = [];

        do {
            $patches[] = $this->readTriangleText($parser, $is3D, $isMeasured, $srid);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return TIN::create($patches, $is3D, $isMeasured, $srid);
    }
}
