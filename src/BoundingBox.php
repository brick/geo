<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\EmptyGeometryException;

/**
 * Represents a 2D or 3D bounding box calculated from a set of points. M coordinates are ignored.
 * This class is immutable.
 */
final class BoundingBox
{
    /**
     * Private constructor. Use BoundingBox::new() to obtain an instance.
     */
    private function __construct(
        public readonly ?CoordinateSystem $cs,
        public readonly ?float $swX,
        public readonly ?float $swY,
        public readonly ?float $swZ,
        public readonly ?float $neX,
        public readonly ?float $neY,
        public readonly ?float $neZ,
    ) {
    }

    /**
     * Creates an empty BoundingBox instance.
     */
    public static function new(): BoundingBox
    {
        return new BoundingBox(
            null,
            null,
            null,
            null,
            null,
            null,
            null,
        );
    }

    /**
     * Returns a copy of this BoundingBox extended with the given Point.
     *
     * @throws CoordinateSystemException
     */
    public function extendedWithPoint(Point $point) : BoundingBox
    {
        if ($point->isEmpty()) {
            return $this;
        }

        $point = $point->withoutM();

        if ($this->cs === null) {
            $cs = $point->coordinateSystem();
        } else {
            $cs = $this->cs;
            if (! $cs->isEqualTo($point->coordinateSystem())) {
                throw CoordinateSystemException::dimensionalityMix($cs, $point->coordinateSystem());
            }
        }

        $x = $point->x();
        $y = $point->y();
        $z = $point->z();

        $swX = ($this->swX === null) ? $x : min($this->swX, $x);
        $swY = ($this->swY === null) ? $y : min($this->swY, $y);

        $neX = ($this->neX === null) ? $x : max($this->neX, $x);
        $neY = ($this->neY === null) ? $y : max($this->neY, $y);

        if ($z !== null) {
            $swZ = ($this->swZ === null) ? $z : min($this->swZ, $z);
            $neZ = ($this->neZ === null) ? $z : max($this->neZ, $z);
        } else {
            $swZ = null;
            $neZ = null;
        }

        if (
            $swX === $this->swX && $swY === $this->swY && $swZ === $this->swZ &&
            $neX === $this->neX && $neY === $this->neY && $neZ === $this->neZ
        ) {
            return $this;
        }

        return new BoundingBox(
            $cs,
            $swX,
            $swY,
            $swZ,
            $neX,
            $neY,
            $neZ,
        );
    }

    /**
     * Returns a copy of this BoundingBox extended with the given BoundingBox.
     *
     * @throws CoordinateSystemException
     */
    public function extendedWithBoundingBox(BoundingBox $boundingBox) : BoundingBox
    {
        if ($boundingBox->isEmpty()) {
            return $this;
        }

        return $this
            ->extendedWithPoint($boundingBox->getSouthWest())
            ->extendedWithPoint($boundingBox->getNorthEast());
    }

    public function isEmpty() : bool
    {
        return $this->cs === null;
    }

    /**
     * Returns the south-west XY or XYZ point.
     *
     * @throws EmptyGeometryException
     */
    public function getSouthWest() : Point
    {
        if ($this->cs === null) {
            throw new EmptyGeometryException('The bounding box is empty.');
        }

        assert($this->swX !== null);
        assert($this->swY !== null);

        if ($this->cs->hasZ()) {
            assert($this->swZ !== null);
            $coords = [$this->swX, $this->swY, $this->swZ];
        } else {
            $coords = [$this->swX, $this->swY];
        }

        return new Point($this->cs, ...$coords);
    }

    /**
     * Returns the north-east XY or XYZ point.
     *
     * @throws EmptyGeometryException
     */
    public function getNorthEast() : Point
    {
        if ($this->cs === null) {
            throw new EmptyGeometryException('The bounding box is empty.');
        }

        if ($this->cs->hasZ()) {
            $coords = [$this->neX, $this->neY, $this->neZ];
        } else {
            $coords = [$this->neX, $this->neY];
        }

        /** @var list<float> $coords */
        return new Point($this->cs, ...$coords);
    }
}
