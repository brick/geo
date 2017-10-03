<?php

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryIOException;
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

/**
 * Base class for WKTWriter and EWKTWriter.
 */
abstract class AbstractWKTWriter
{
    /**
     * Whether to pretty-print (add extra spaces for readability) the WKT.
     *
     * @var bool
     */
    private $prettyPrint = true;

    /**
     * A space if prettyPrint is true, an empty string otherwise.
     *
     * @var string
     */
    protected $prettyPrintSpace = ' ';

    /**
     * @param bool $prettyPrint
     *
     * @return void
     */
    public function setPrettyPrint($prettyPrint)
    {
        $this->prettyPrint = (bool) $prettyPrint;
        $this->prettyPrintSpace = $prettyPrint ? ' ' : '';
    }

    /**
     * @param Geometry $geometry The geometry to export as WKT.
     *
     * @return string The WKT representation of the given geometry.
     *
     * @throws GeometryIOException If the given geometry cannot be exported as WKT.
     */
    abstract public function write(Geometry $geometry);

    /**
     * @param Geometry $geometry The geometry to export as WKT.
     *
     * @return string The WKT representation of the given geometry.
     *
     * @throws GeometryIOException If the given geometry cannot be exported as WKT.
     */
    protected function doWrite(Geometry $geometry)
    {
        $type = strtoupper($geometry->geometryType());

        $cs = $geometry->coordinateSystem();

        $hasZ = $cs->hasZ();
        $hasM = $cs->hasM();

        $dimensionality = '';

        if ($hasZ || $hasM) {
            $dimensionality .= ' ';

            if ($hasZ) {
                $dimensionality .= 'Z';
            }
            if ($hasM) {
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
        } elseif ($geometry instanceof CurvePolygon) {
            $data = $this->writeCurvePolygon($geometry);
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
            throw GeometryIOException::unsupportedGeometryType($geometry->geometryType());
        }

        return $type . $dimensionality . $this->prettyPrintSpace . '(' . $data . ')';
    }

    /**
     * @param Point $point
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
     * @param LineString $lineString
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
     * @param CircularString $circularString
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
     * @param CompoundCurve $compoundCurve
     *
     * @return string
     *
     * @throws GeometryIOException
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
                throw new GeometryIOException('Only LineString and CircularString are allowed in CompoundCurve WKT.');
            }
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    /**
     * @param Polygon $polygon
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
     * @param CurvePolygon $curvePolygon
     *
     * @return string
     */
    private function writeCurvePolygon(CurvePolygon $curvePolygon)
    {
        $result = [];

        foreach ($curvePolygon as $ring) {
            if ($ring instanceof LineString) {
                $result[] = '(' . $this->writeLineString($ring) . ')';
            } else {
                $result[] = $this->doWrite($ring);
            }
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    /**
     * @param MultiPoint $multiPoint
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
     * @param MultiLineString $multiLineString
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
     * @param MultiPolygon $multiPolygon
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
     * @param GeometryCollection $collection
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
     * @param PolyhedralSurface $polyhedralSurface
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
