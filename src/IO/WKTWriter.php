<?php

namespace Brick\Geo\IO;

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
use Brick\Geo\Exception\GeometryException;

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
    protected $prettyPrintSpace = ' ';

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
     * @param Geometry $geometry
     *
     * @return string
     *
     * @throws GeometryException
     */
    public function write(Geometry $geometry)
    {
        return $this->doWrite($geometry);
    }

    /**
     * @param \Brick\Geo\Geometry $geometry
     *
     * @return string
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    protected function doWrite(Geometry $geometry)
    {
        $type = strtoupper($geometry->geometryType());

        $z = $geometry->is3D();
        $m = $geometry->isMeasured();

        $dimensionality = '';

        if ($z || $m) {
            $dimensionality .= ' ';

            if ($z) {
                $dimensionality .= 'Z';
            }
            if ($m) {
                $dimensionality .= 'M';
            }
        }

        if ($geometry instanceof GeometryCollection) {
            $isEmpty = ($geometry->numGeometries() === 0);
        } else {
            $isEmpty = $geometry->isEmpty();
        }

        if ($isEmpty) {
            return $type . $dimensionality . ' EMPTY';
        }

        if ($geometry instanceof Point) {
            $data = $this->writePoint($geometry);
        } elseif ($geometry instanceof LineString) {
            $data = $this->writeLineString($geometry);
        } elseif ($geometry instanceof CircularString) {
            $data = $this->writeCircularString($geometry);
        } elseif ($geometry instanceof CompoundCurve) {
            $data = $this->writeCompoundCurve($geometry);
        } elseif ($geometry instanceof Triangle) {
            $data = $this->writePolygon($geometry);
        } elseif ($geometry instanceof Polygon) {
            $data = $this->writePolygon($geometry);
        } elseif ($geometry instanceof MultiPoint) {
            $data = $this->writeMultiPoint($geometry);
        } elseif ($geometry instanceof MultiLineString) {
            $data = $this->writeMultiLineString($geometry);
        } elseif ($geometry instanceof MultiPolygon) {
            $data = $this->writeMultiPolygon($geometry);
        } elseif ($geometry instanceof GeometryCollection) {
            $data = $this->writeGeometryCollection($geometry);
        } elseif ($geometry instanceof TIN) {
            $data = $this->writePolyhedralSurface($geometry);
        } elseif ($geometry instanceof PolyhedralSurface) {
            $data = $this->writePolyhedralSurface($geometry);
        } else {
            throw GeometryException::unsupportedGeometryType($geometry->geometryType());
        }

        return $type . $dimensionality . $this->prettyPrintSpace . '(' . $data . ')';
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
     * @param \Brick\Geo\CircularString $circularString
     *
     * @return string
     */
    private function writeCircularString(CircularString $circularString)
    {
        $result = [];

        foreach ($circularString as $point) {
            $result[] = $this->writePoint($point);
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    /**
     * @param \Brick\Geo\CompoundCurve $compoundCurve
     *
     * @return string
     *
     * @throws GeometryException
     */
    private function writeCompoundCurve(CompoundCurve $compoundCurve)
    {
        $result = [];

        foreach ($compoundCurve as $curve) {
            if ($curve instanceof LineString) {
                $result[] = '(' . $this->writeLineString($curve). ')';
            } elseif ($curve instanceof CircularString) {
                $result[] = $this->doWrite($curve);
            } else {
                throw new GeometryException('Only LineString and CircularString are allowed in CompoundCurve WKT.');
            }
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
            $result[] = $this->doWrite($geometry);
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    /**
     * @param \Brick\Geo\PolyhedralSurface $polyhedralSurface
     *
     * @return string
     */
    private function writePolyhedralSurface(PolyhedralSurface $polyhedralSurface)
    {
        $result = [];

        foreach ($polyhedralSurface as $patch) {
            $result[] = '(' . $this->writePolygon($patch) . ')';
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }
}
