<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;

/**
 * A CompoundCurve is a collection of zero or more continuous CircularString or LineString instances.
 */
class CompoundCurve extends Curve
{
    /**
     * The Curves that compose this CompoundCurve.
     *
     * This array can be empty.
     *
     * @var Curve[]
     */
    protected $curves = [];

    /**
     * Class constructor.
     *
     * The coordinate system of each of the curves must match the one of the CompoundCurve.
     *
     * @param CoordinateSystem $cs        The coordinate system of the CompoundCurve.
     * @param Curve            ...$curves The curves that compose the CompoundCurve.
     *
     * @throws GeometryException
     */
    public function __construct(CoordinateSystem $cs, Curve ...$curves)
    {
        parent::__construct($cs, ! $curves);

        if (! $curves) {
            return;
        }

        /** @var Curve|null $previousCurve */
        $previousCurve = null;

        foreach ($curves as $curve) {
            $cs->checkMatches($curve->coordinateSystem());

            if ($previousCurve) {
                $endPoint = $previousCurve->endPoint();
                $startPoint = $curve->startPoint();

                if ($endPoint != $startPoint) { // on purpose by-value comparison!
                    throw new GeometryException('Incontinuous compound curve.');
                }
            }

            $previousCurve = $curve;
        }

        $this->curves = $curves;
    }

    /**
     * Returns a CompoundCurve composed of the given curves.
     *
     * All curves must be using the same coordinate system.
     * The coordinate system being inferred from the curves, an empty curve list is not allowed.
     * To create an empty CompoundCurve, use the class constructor instead.
     *
     * @param Curve ...$curves The curves that compose the CompoundCurve.
     *
     * @return CompoundCurve
     *
     * @throws GeometryException
     */
    public static function of(Curve ...$curves)
    {
        if (! $curves) {
            throw GeometryException::atLeastOneGeometryExpected(static::class, __FUNCTION__);
        }

        return new CompoundCurve($curves[0]->coordinateSystem(), ...$curves);
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
     * Returns the number of curves in this CompoundCurve.
     *
     * Required by interface Countable.
     *
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->curves);
    }

    /**
     * Returns an iterator for the curves in this CompoundCurve.
     *
     * Required by interface IteratorAggregate.
     *
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->curves);
    }
}
