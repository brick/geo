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
        $zm = $parser->getOptionalNextWord();

        if ($zm === null) {
            $is3D = false;
            $isMeasured = false;
        } elseif ($zm === 'Z') {
            $is3D = true;
            $isMeasured = false;
        } elseif ($zm === 'M') {
            $is3D = false;
            $isMeasured = true;
        } elseif ($zm === 'ZM') {
            $is3D = true;
            $isMeasured = true;
        } else {
            throw new GeometryException('Unexpected word in WKT: ' . $zm);
        }

        switch ($geometryType) {
            case 'POINT':
                return $this->readPointText($parser, $is3D, $isMeasured, $srid);

            case 'LINESTRING':
                return $this->readLineStringText($parser, $is3D, $isMeasured, $srid);

            case 'POLYGON':
                return $this->readPolygonText($parser, $is3D, $isMeasured, $srid);

            case 'MULTIPOINT':
                return $this->readMultiPointText($parser, $is3D, $isMeasured, $srid);

            case 'MULTILINESTRING':
                return $this->readMultiLineStringText($parser, $is3D, $isMeasured, $srid);

            case 'MULTIPOLYGON':
                return $this->readMultiPolygonText($parser, $is3D, $isMeasured, $srid);

            case 'GEOMETRYCOLLECTION':
                return $this->readGeometryCollectionText($parser, $is3D, $isMeasured, $srid);
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

        return LineString::factory($points);
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

        return Polygon::factory($rings);
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
            $geometry = $this->readGeometry($parser, $srid);

            if ($geometry->is3D() !== $is3D || $geometry->isMeasured() !== $isMeasured) {
                throw GeometryException::collectionDimensionalityMix($is3D, $isMeasured, $srid, $geometry);
            }

            $geometries[] = $geometry;
            $nextToken = $parser->getNextCloserOrComma();
        } while ($nextToken === ',');

        return GeometryCollection::factory($geometries);
    }
}
