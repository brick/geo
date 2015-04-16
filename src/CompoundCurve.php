<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;

/**
 * A CompoundCurve is a collection of zero or more continuous CircularString or LineString instances.
 */
class CompoundCurve extends Curve implements \Countable, \IteratorAggregate
{
    /**
     * The Curves that compose this CompoundCurve.
     *
     * @var Curve[]
     */
    protected $curves = [];

    /**
     * @param Curve[]               $curves
     * @param CoordinateSystem|null $cs
     *
     * @return CompoundCurve
     *
     * @throws GeometryException
     */
    public static function create(array $curves, CoordinateSystem $cs = null)
    {
        $cs = self::checkGeometries($curves, Curve::class, $cs);

        /** @var Curve|null $previousCurve */
        $previousCurve = null;

        foreach ($curves as $curve) {
            if ($previousCurve) {
                $endPoint = $previousCurve->endPoint();
                $startPoint = $curve->startPoint();

                if ($endPoint != $startPoint) { // on purpose by-value comparison!
                    throw new GeometryException('Incontinuous compound curve.');
                }
            }

            $previousCurve = $curve;
        }

        $compoundCurve = new CompoundCurve($cs, ! $curves);
        $compoundCurve->curves = array_values($curves);

        return $compoundCurve;
    }

    /**
     * {@inheritdoc}
     */
    public function startPoint()
    {
        if ($this->isEmpty) {
            throw new GeometryException('The CompoundCurve is empty and has no start point.');
        }

        return $this->curves[0]->startPoint();
    }

    /**
     * {@inheritdoc}
     */
    public function endPoint()
    {
        if ($this->isEmpty) {
            throw new GeometryException('The CompoundCurve is empty and has no end point.');
        }

        $count = count($this->curves);

        return $this->curves[$count - 1]->endPoint();
    }

    /**
     * Returns the number of Curves in this CompoundCurve.
     *
     * @return integer
     */
    public function numCurves()
    {
        return count($this->curves);
    }

    /**
     * Returns the specified Curve N in this CompoundCurve.
     *
     * @param integer $n The curve number, 1-based.
     *
     * @return Curve
     *
     * @throws GeometryException If there is no Curve at this index.
     */
    public function curveN($n)
    {
        $n = (int) $n;

        if (! isset($this->curves[$n - 1])) {
            throw new GeometryException('There is no Curve in this CompoundCurve at index ' . $n);
        }

        return $this->curves[$n - 1];
    }

    /**
     * Returns the curves that compose this CompoundCurve.
     *
     * @return Curve[]
     */
    public function curves()
    {
        return $this->curves;
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'CompoundCurve';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $result = [];

        foreach ($this->curves as $curve) {
            $result[] = $curve->toArray();
        }

        return $result;
    }

    /**
     * Alias for `numCurves()`, required by interface Countable.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->curves);
    }

    /**
     * Returns an iterator for the curves, required by interface IteratorAggregate.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->curves);
    }
}
