<?php

namespace Brick\Geo\IO;

use Brick\Geo\Point;
use Brick\Geo\LineString;
use Brick\Geo\Polygon;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPolygon;
use Brick\Geo\GeometryCollection;
use Brick\Geo\Exception\GeometryException;

/**
 * Builds geometries out of Well-Known Text strings.
 */
abstract class WKTReader
{
    /**
     * @param string $wkt
     *
     * @return \Brick\Geo\Geometry
     *
     * @throws GeometryException
     */
    public static function read($wkt)
    {
        $parser = new WKTParser($wkt);
        $geometry = self::readGeometry($parser);

        if (! $parser->isEndOfStream()) {
            throw GeometryException::invalidWkt();
        }

        return $geometry;
    }

    /**
     * @param WKTParser $parser
     *
     * @return \Brick\Geo\Geometry
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public static function readGeometry(WKTParser $parser)
    {
        $geometryType = $parser->getNextWord();

        switch (strtoupper($geometryType)) {
            case 'POINT':
                return self::readPointText($parser);
            case 'LINESTRING':
                return self::readLineStringText($parser);
            case 'POLYGON':
                return self::readPolygonText($parser);
            case 'MULTIPOINT':
                return self::readMultiPointText($parser);
            case 'MULTILINESTRING':
                return self::readMultiLineStringText($parser);
            case 'MULTIPOLYGON':
                return self::readMultiPolygonText($parser);
            case 'GEOMETRYCOLLECTION':
                return self::readGeometryCollectionText($parser);
        }

        throw new GeometryException('Unknown geometry type: ' . $geometryType);
    }

    /**
     * x y
     *
     * @param WKTParser $parser
     *
     * @return \Brick\Geo\Point
     */
    private static function readPoint(WKTParser $parser)
    {
        $x = $parser->getNextNumber();
        $y = $parser->getNextNumber();

        return Point::factory($x, $y);
    }

    /**
     * (x y)
     *
     * @param WKTParser  $parser
     *
     * @return \Brick\Geo\Point
     */
    private static function readPointText(WKTParser $parser)
    {
        $parser->matchOpener();
        $point = self::readPoint($parser);
        $parser->matchCloser();

        return $point;
    }

    /**
     * (x y, ...)
     *
     * @param WKTParser $parser
     *
     * @return \Brick\Geo\Point[]
     */
    private static function readMultiPoint(WKTParser $parser)
    {
        $parser->matchOpener();
        $points = [];

        do {
            $points[] = self::readPoint($parser);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken == ',');

        return $points;
    }

    /**
     * (x y, ...)
     *
     * @param WKTParser $parser
     *
     * @return \Brick\Geo\LineString
     */
    private static function readLineStringText(WKTParser $parser)
    {
        $points = self::readMultiPoint($parser);

        return LineString::factory($points);
    }

    /**
     * (x y, ...)
     *
     * @param WKTParser $parser
     *
     * @return \Brick\Geo\MultiPoint
     */
    private static function readMultiPointText(WKTParser $parser)
    {
        $points = self::readMultiPoint($parser);

        return MultiPoint::factory($points);
    }

    /**
     * ((x y, ...), ...)
     *
     * @param WKTParser $parser
     *
     * @return \Brick\Geo\LineString[]
     */
    private static function readMultiLineString(WKTParser $parser)
    {
        $parser->matchOpener();
        $lineStrings = [];

        do {
            $lineStrings[] = self::readLineStringText($parser);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken == ',');

        return $lineStrings;
    }

    /**
     * ((x y, ...), ...)
     *
     * @param WKTParser $parser
     *
     * @return \Brick\Geo\Polygon
     */
    private static function readPolygonText(WKTParser $parser)
    {
        $rings = self::readMultiLineString($parser);

        return Polygon::factory($rings);
    }

    /**
     * ((x y, ...), ...)
     *
     * @param WKTParser $parser
     *
     * @return \Brick\Geo\MultiLineString
     */
    private static function readMultiLineStringText(WKTParser $parser)
    {
        $rings = self::readMultiLineString($parser);

        return MultiLineString::factory($rings);
    }

    /**
     * (((x y, ...), ...), ...)
     *
     * @param WKTParser $parser
     *
     * @return \Brick\Geo\MultiPolygon
     */
    private static function readMultiPolygonText(WKTParser $parser)
    {
        $parser->matchOpener();
        $polygons = [];

        do {
            $polygons[] = self::readPolygonText($parser);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken == ',');

        return MultiPolygon::factory($polygons);
    }

    /**
     * @param WKTParser $parser
     *
     * @return \Brick\Geo\GeometryCollection
     */
    private static function readGeometryCollectionText(WKTParser $parser)
    {
        $parser->matchOpener();
        $geometries = [];

        do {
            $geometries[] = self::readGeometry($parser);
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken == ',');

        return GeometryCollection::factory($geometries);
    }
}
