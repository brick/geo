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

/**
 * Converter class from Geometry to WKT.
 */
class WKTWriter
{
    /**
     * Whether to pretty-print (add extra spaces for readability) the WKT.
     *
     * @var boolean
     */
    private $prettyPrint = true;

    /**
     * A space if prettyPrint is true, an empty string otherwise.
     *
     * @var string
     */
    private $prettyPrintSpace = ' ';

    /**
     * @param boolean $prettyPrint
     *
     * @return void
     */
    public function setPrettyPrint($prettyPrint)
    {
        $this->prettyPrint = (bool) $prettyPrint;
        $this->prettyPrintSpace = $prettyPrint ? ' ' : '';
    }

    /**
     * @param \Brick\Geo\Geometry $geometry
     *
     * @return string
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function write(Geometry $geometry)
    {
        if ($geometry instanceof Point) {
            $type = 'POINT';
            $data = $this->writePoint($geometry);
        } elseif ($geometry instanceof LineString) {
            $type = 'LINESTRING';
            $data = $this->writeLineString($geometry);
        } elseif ($geometry instanceof Polygon) {
            $type = 'POLYGON';
            $data = $this->writePolygon($geometry);
        } elseif ($geometry instanceof MultiPoint) {
            $type = 'MULTIPOINT';
            $data = $this->writeMultiPoint($geometry);
        } elseif ($geometry instanceof MultiLineString) {
            $type = 'MULTILINESTRING';
            $data = $this->writeMultiLineString($geometry);
        } elseif ($geometry instanceof MultiPolygon) {
            $type = 'MULTIPOLYGON';
            $data = $this->writeMultiPolygon($geometry);
        } elseif ($geometry instanceof GeometryCollection) {
            $type = 'GEOMETRYCOLLECTION';
            $data = $this->writeGeometryCollection($geometry);
        } else {
            throw GeometryException::unsupportedGeometryType($geometry);
        }

        $z = $geometry->is3D();
        $m = $geometry->isMeasured();

        $wkt = $type;

        if ($z || $m) {
            $wkt .= ' ';

            if ($z) {
                $wkt .= 'Z';
            }
            if ($m) {
                $wkt .= 'M';
            }
        }

        $wkt .= $this->prettyPrintSpace;

        $wkt .= '(' . $data . ')';

        return $wkt;
    }

    /**
     * @param \Brick\Geo\Point $point
     *
     * @return string
     */
    private function writePoint(Point $point)
    {
        $result = $point->x() . ' ' . $point->y();

        if (null !== $z = $point->z()) {
            $result .= ' ' . $z;
        }

        if (null !== $m = $point->m()) {
            $result .= ' ' . $m;
        }

        return $result;
    }

    /**
     * @param \Brick\Geo\LineString $lineString
     *
     * @return string
     */
    private function writeLineString(LineString $lineString)
    {
        $result = [];

        foreach ($lineString as $point) {
            $result[] = $this->writePoint($point);
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    /**
     * @param \Brick\Geo\Polygon $polygon
     *
     * @return string
     */
    private function writePolygon(Polygon $polygon)
    {
        $result = [];

        foreach ($polygon as $ring) {
            $result[] = '(' . $this->writeLineString($ring) . ')';
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    /**
     * @param \Brick\Geo\MultiPoint $multiPoint
     *
     * @return string
     */
    private function writeMultiPoint(MultiPoint $multiPoint)
    {
        $result = [];

        foreach ($multiPoint as $point) {
            $result[] = $this->writePoint($point);
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    /**
     * @param \Brick\Geo\MultiLineString $multiLineString
     *
     * @return string
     */
    private function writeMultiLineString(MultiLineString $multiLineString)
    {
        $result = [];

        foreach ($multiLineString as $lineString) {
            $result[] = '(' . $this->writeLineString($lineString) . ')';
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    /**
     * @param \Brick\Geo\MultiPolygon $multiPolygon
     *
     * @return string
     */
    private function writeMultiPolygon(MultiPolygon $multiPolygon)
    {
        $result = [];

        foreach ($multiPolygon as $polygon) {
            $result[] = '(' . $this->writePolygon($polygon) . ')';
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    /**
     * @param \Brick\Geo\GeometryCollection $collection
     *
     * @return string
     */
    private function writeGeometryCollection(GeometryCollection $collection)
    {
        $result = [];

        foreach ($collection as $geometry) {
            $result[] = $this->write($geometry);
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }
}
