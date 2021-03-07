<?php

declare(strict_types=1);

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
     * A space if prettyPrint is true, an empty string otherwise.
     */
    protected string $prettyPrintSpace = ' ';

    public function setPrettyPrint(bool $prettyPrint) : void
    {
        $this->prettyPrintSpace = $prettyPrint ? ' ' : '';
    }

    /**
     * @param Geometry $geometry The geometry to export as WKT.
     *
     * @return string The WKT representation of the given geometry.
     *
     * @throws GeometryIOException If the given geometry cannot be exported as WKT.
     */
    abstract public function write(Geometry $geometry) : string;

    /**
     * @param Geometry $geometry The geometry to export as WKT.
     *
     * @return string The WKT representation of the given geometry.
     *
     * @throws GeometryIOException If the given geometry cannot be exported as WKT.
     */
    protected function doWrite(Geometry $geometry) : string
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
     * @param Point $point The point. Must not be empty.
     */
    private function writePoint(Point $point) : string
    {
        /** @psalm-suppress PossiblyNullOperand */
        $result = $point->x() . ' ' . $point->y();

        if (null !== $z = $point->z()) {
            $result .= ' ' . $z;
        }

        if (null !== $m = $point->m()) {
            $result .= ' ' . $m;
        }

        return $result;
    }

    private function writeLineString(LineString $lineString) : string
    {
        $result = [];

        foreach ($lineString as $point) {
            $result[] = $this->writePoint($point);
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    private function writeCircularString(CircularString $circularString) : string
    {
        $result = [];

        foreach ($circularString as $point) {
            $result[] = $this->writePoint($point);
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    /**
     * @throws GeometryIOException
     */
    private function writeCompoundCurve(CompoundCurve $compoundCurve) : string
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

    private function writePolygon(Polygon $polygon) : string
    {
        $result = [];

        foreach ($polygon as $ring) {
            $result[] = '(' . $this->writeLineString($ring) . ')';
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    private function writeCurvePolygon(CurvePolygon $curvePolygon) : string
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

    private function writeMultiPoint(MultiPoint $multiPoint) : string
    {
        $result = [];

        foreach ($multiPoint as $point) {
            $result[] = $this->writePoint($point);
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    private function writeMultiLineString(MultiLineString $multiLineString) : string
    {
        $result = [];

        foreach ($multiLineString as $lineString) {
            $result[] = '(' . $this->writeLineString($lineString) . ')';
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    private function writeMultiPolygon(MultiPolygon $multiPolygon) : string
    {
        $result = [];

        foreach ($multiPolygon as $polygon) {
            $result[] = '(' . $this->writePolygon($polygon) . ')';
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    private function writeGeometryCollection(GeometryCollection $collection) : string
    {
        $result = [];

        foreach ($collection as $geometry) {
            $result[] = $this->doWrite($geometry);
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }

    private function writePolyhedralSurface(PolyhedralSurface $polyhedralSurface) : string
    {
        $result = [];

        foreach ($polyhedralSurface as $patch) {
            $result[] = '(' . $this->writePolygon($patch) . ')';
        }

        return implode(',' . $this->prettyPrintSpace, $result);
    }
}
