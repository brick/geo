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
abstract class WktReader
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
        $parser = new WktParser($wkt);
        $geometry = self::readGeometry($parser);

        if (! $parser->isEndOfStream()) {
            throw GeometryException::invalidWkt();
        }

        return $geometry;
    }

    /**
     * @param WktParser $parser
     *
     * @return \Brick\Geo\Geometry
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public static function readGeometry(WktParser $parser)
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
     * @param WktParser $parser
     *
     * @return \Brick\Geo\Point
     */
    protected static function readPoint(WktParser $parser)
    {
        $x = $parser->getNextNumber();
        $y = $parser->getNextNumber();

        return Point::factory($x, $y);
    }

    /**
     * (x y)
     *
     * @param WktParser  $parser
     *
     * @return \Brick\Geo\Point
     */
    protected static function readPointText(WktParser $parser)
    {
        $parser->matchOpener();
        $point = self::readPoint($parser);
        $parser->matchCloser();

        return $point;
    }

    /**
     * (x y, ...)
     *
     * @param WktParser $parser
     *
     * @return \Brick\Geo\Point[]
     */
    protected static function readMultiPoint(WktParser $parser)
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
     * @param WktParser $parser
     *
     * @return \Brick\Geo\LineString
     */
    protected static function readLineStringText(WktParser $parser)
    {
        $points = self::readMultiPoint($parser);

        return LineString::factory($points);
    }

    /**
     * (x y, ...)
     *
     * @param WktParser $parser
     *
     * @return \Brick\Geo\MultiPoint
     */
    protected static function readMultiPointText(WktParser $parser)
    {
        $points = self::readMultiPoint($parser);

        return MultiPoint::factory($points);
    }

    /**
     * ((x y, ...), ...)
     *
     * @param WktParser $parser
     *
     * @return \Brick\Geo\LineString[]
     */
    protected static function readMultiLineString(WktParser $parser)
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
     * @param WktParser $parser
     *
     * @return \Brick\Geo\Polygon
     */
    protected static function readPolygonText(WktParser $parser)
    {
        $rings = self::readMultiLineString($parser);

        return Polygon::factory($rings);
    }

    /**
     * ((x y, ...), ...)
     *
     * @param WktParser $parser
     *
     * @return \Brick\Geo\MultiLineString
     */
    protected static function readMultiLineStringText(WktParser $parser)
    {
        $rings = self::readMultiLineString($parser);

        return MultiLineString::factory($rings);
    }

    /**
     * (((x y, ...), ...), ...)
     *
     * @param WktParser $parser
     *
     * @return \Brick\Geo\MultiPolygon
     */
    protected static function readMultiPolygonText(WktParser $parser)
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
     * @param WktParser $parser
     *
     * @return \Brick\Geo\GeometryCollection
     */
    protected static function readGeometryCollectionText(WktParser $parser)
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
