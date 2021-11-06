<?php

declare(strict_types=1);

namespace Brick\Geo;

use ArrayIterator;
use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\EmptyGeometryException;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Exception\NoSuchGeometryException;

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
     * @psalm-var list<Curve>
     *
     * @var Curve[]
     */
    protected array $curves = [];

    /**
     * The coordinate system of each of the curves must match the one of the CompoundCurve.
     *
     * @param CoordinateSystem $cs        The coordinate system of the CompoundCurve.
     * @param Curve            ...$curves The curves that compose the CompoundCurve.
     *
     * @throws EmptyGeometryException    If any of the input curves is empty.
     * @throws InvalidGeometryException  If the compound curve is not continuous.
     * @throws CoordinateSystemException If different coordinate systems are used.
     */
    public function __construct(CoordinateSystem $cs, Curve ...$curves)
    {
        parent::__construct($cs, ! $curves);

        if (! $curves) {
            return;
        }

        CoordinateSystem::check($this, ...$curves);

        /** @var Curve|null $previousCurve */
        $previousCurve = null;

        foreach ($curves as $curve) {
            if ($previousCurve) {
                $endPoint = $previousCurve->endPoint();
                $startPoint = $curve->startPoint();

                if ($endPoint != $startPoint) { // on purpose by-value comparison!
                    throw new InvalidGeometryException('Incontinuous compound curve.');
                }
            }

            $previousCurve = $curve;
        }

        $this->curves = $curves;
    }

    /**
     * Creates a non-empty CompoundCurve composed of the given curves.
     *
     * @param Curve    $curve1 The first curve.
     * @param Curve ...$curveN The subsequent curves, if any.
     *
     * @throws EmptyGeometryException    If any of the input curves is empty.
     * @throws InvalidGeometryException  If the compound curve is not continuous.
     * @throws CoordinateSystemException If the curves use different coordinate systems.
     */
    public static function of(Curve $curve1, Curve ...$curveN) : CompoundCurve
    {
        return new CompoundCurve($curve1->coordinateSystem(), $curve1, ...$curveN);
    }

    public function startPoint() : Point
    {
        if ($this->isEmpty) {
            throw new EmptyGeometryException('The CompoundCurve is empty and has no start point.');
        }

        return $this->curves[0]->startPoint();
    }

    public function endPoint() : Point
    {
        if ($this->isEmpty) {
            throw new EmptyGeometryException('The CompoundCurve is empty and has no end point.');
        }

        $count = count($this->curves);

        return $this->curves[$count - 1]->endPoint();
    }

    /**
     * Returns the number of Curves in this CompoundCurve.
     */
    public function numCurves() : int
    {
        return count($this->curves);
    }

    /**
     * Returns the specified Curve N in this CompoundCurve.
     *
     * @param int $n The curve number, 1-based.
     *
     * @throws NoSuchGeometryException If there is no Curve at this index.
     */
    public function curveN(int $n) : Curve
    {
        if (! isset($this->curves[$n - 1])) {
            throw new NoSuchGeometryException('There is no Curve in this CompoundCurve at index ' . $n);
        }

        return $this->curves[$n - 1];
    }

    /**
     * Returns the curves that compose this CompoundCurve.
     *
     * @return Curve[]
     */
    public function curves() : array
    {
        return $this->curves;
    }

    /**
     * @noproxy
     */
    public function geometryType() : string
    {
        return 'CompoundCurve';
    }

    /**
     * @noproxy
     */
    public function geometryTypeBinary() : int
    {
        return Geometry::COMPOUNDCURVE;
    }

    public function toXY(): CompoundCurve
    {
        if ($this->coordinateDimension() === 2) {
            return $this;
        }

        $cs = $this->coordinateSystem
            ->withZ(false)
            ->withM(false);

        $curves = array_map(fn(Curve $curve) => $curve->toXY(), $this->curves);

        return new CompoundCurve($cs, ...$curves);
    }

    public function withoutZ(): CompoundCurve
    {
        if (! $this->coordinateSystem->hasZ()) {
            return $this;
        }

        $cs = $this->coordinateSystem->withZ(false);

        $curves = array_map(fn(Curve $curve) => $curve->withoutZ(), $this->curves);

        return new CompoundCurve($cs, ...$curves);
    }

    public function withoutM(): CompoundCurve
    {
        if (! $this->coordinateSystem->hasM()) {
            return $this;
        }

        $cs = $this->coordinateSystem->withM(false);

        $curves = array_map(fn(Curve $curve) => $curve->withoutM(), $this->curves);

        return new CompoundCurve($cs, ...$curves);
    }

    public function getBoundingBox() : BoundingBox
    {
        $boundingBox = new BoundingBox();

        foreach ($this->curves as $curve) {
            $boundingBox = $boundingBox->extendedWithBoundingBox($curve->getBoundingBox());
        }

        return $boundingBox;
    }

    public function toArray() : array
    {
        $result = [];

        foreach ($this->curves as $curve) {
            $result[] = $curve->toArray();
        }

        return $result;
    }

    public function swapXY() : Geometry
    {
        $that = clone $this;

        foreach ($that->curves as & $curve) {
            $curve = $curve->swapXY();
        }

        return $that;
    }

    /**
     * Returns the number of curves in this CompoundCurve.
     *
     * Required by interface Countable.
     */
    public function count() : int
    {
        return count($this->curves);
    }

    /**
     * Returns an iterator for the curves in this CompoundCurve.
     *
     * Required by interface IteratorAggregate.
     *
     * @psalm-return ArrayIterator<int, Curve>
     */
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->curves);
    }
}
