<?php

declare(strict_types=1);

namespace Brick\Geo;

use ArrayIterator;
use Brick\Geo\Attribute\NoProxy;
use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\EmptyGeometryException;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Exception\NoSuchGeometryException;
use Brick\Geo\Projector\Projector;
use Override;

/**
 * A CompoundCurve is a collection of zero or more continuous CircularString or LineString instances.
 *
 * @template-implements \IteratorAggregate<int<0, max>, LineString|CircularString>
 */
class CompoundCurve extends Curve implements \Countable, \IteratorAggregate
{
    /**
     * The Curves that compose this CompoundCurve.
     *
     * This array can be empty.
     *
     * @var list<LineString|CircularString>
     */
    protected array $curves = [];

    /**
     * The coordinate system of each of the curves must match the one of the CompoundCurve.
     *
     * @param CoordinateSystem             $cs     The coordinate system of the CompoundCurve.
     * @param LineString|CircularString ...$curves The curves that compose the CompoundCurve.
     *
     * @throws EmptyGeometryException    If any of the input curves is empty.
     * @throws InvalidGeometryException  If the compound curve is not continuous.
     * @throws CoordinateSystemException If different coordinate systems are used.
     */
    public function __construct(CoordinateSystem $cs, LineString|CircularString ...$curves)
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

        $this->curves = array_values($curves);
    }

    /**
     * Creates a non-empty CompoundCurve composed of the given curves.
     *
     * @param LineString|CircularString    $curve1 The first curve.
     * @param LineString|CircularString ...$curveN The subsequent curves, if any.
     *
     * @throws EmptyGeometryException    If any of the input curves is empty.
     * @throws InvalidGeometryException  If the compound curve is not continuous.
     * @throws CoordinateSystemException If the curves use different coordinate systems.
     */
    public static function of(LineString|CircularString $curve1, LineString|CircularString ...$curveN) : CompoundCurve
    {
        return new CompoundCurve($curve1->coordinateSystem(), $curve1, ...$curveN);
    }

    #[Override]
    public function startPoint() : Point
    {
        if (count($this->curves) === 0) {
            throw new EmptyGeometryException('The CompoundCurve is empty and has no start point.');
        }

        return $this->curves[0]->startPoint();
    }

    #[Override]
    public function endPoint() : Point
    {
        $count = count($this->curves);

        if ($count === 0) {
            throw new EmptyGeometryException('The CompoundCurve is empty and has no end point.');
        }

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
    public function curveN(int $n) : LineString|CircularString
    {
        if (! isset($this->curves[$n - 1])) {
            throw new NoSuchGeometryException('There is no Curve in this CompoundCurve at index ' . $n);
        }

        return $this->curves[$n - 1];
    }

    /**
     * Returns the curves that compose this CompoundCurve.
     *
     * @return list<LineString|CircularString>
     */
    public function curves() : array
    {
        return $this->curves;
    }

    #[NoProxy, Override]
    public function geometryType() : string
    {
        return 'CompoundCurve';
    }

    #[NoProxy, Override]
    public function geometryTypeBinary() : int
    {
        return Geometry::COMPOUNDCURVE;
    }

    #[Override]
    public function getBoundingBox() : BoundingBox
    {
        $boundingBox = BoundingBox::new();

        foreach ($this->curves as $curve) {
            $boundingBox = $boundingBox->extendedWithBoundingBox($curve->getBoundingBox());
        }

        return $boundingBox;
    }

    /**
     * @return list<list<list<float>>>
     */
    #[Override]
    public function toArray() : array
    {
        return array_map(
            fn (Curve $curve) => $curve->toArray(),
            $this->curves,
        );
    }

    #[Override]
    public function project(Projector $projector): CompoundCurve
    {
        return new CompoundCurve(
            $projector->getTargetCoordinateSystem($this->coordinateSystem),
            ...array_map(
                fn (Curve $curve) => $curve->project($projector),
                $this->curves,
            ),
        );
    }

    /**
     * Returns the number of curves in this CompoundCurve.
     */
    #[Override]
    public function count() : int
    {
        return count($this->curves);
    }

    /**
     * Returns an iterator for the curves in this CompoundCurve.
     *
     * @return ArrayIterator<int<0, max>, LineString|CircularString>
     */
    #[Override]
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->curves);
    }

    /**
     * Returns a copy of this CompoundCurve, with the given curves added.
     */
    public function withAddedCurves(LineString|CircularString ...$curves): CompoundCurve
    {
        return new CompoundCurve($this->coordinateSystem, ...$this->curves, ...$curves);
    }
}
