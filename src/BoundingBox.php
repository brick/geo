<?php

declare(strict_types=1);

namespace Brick\Geo;

use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\EmptyGeometryException;

/**
 * Represents a 2D bounding box calculated from a set of points.
 * This class is immutable.
 */
class BoundingBox
{
    private ?float $swX = null;

    private ?float $swY = null;

    private ?float $neX = null;

    private ?float $neY = null;

    private ?int $srid = null;

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

        if ($this->srid === null) {
            $this->srid = $point->SRID();
        } elseif ($this->srid !== $point->SRID()) {
            throw CoordinateSystemException::sridMix($this->srid, $point->SRID());
        }

        $x = $point->x();
        $y = $point->y();

        $swX = ($this->swX === null) ? $x : min($this->swX, $x);
        $swY = ($this->swY === null) ? $y : min($this->swY, $y);

        $neX = ($this->neX === null) ? $x : max($this->neX, $x);
        $neY = ($this->neY === null) ? $y : max($this->neY, $y);

        if ($swX === $this->swX && $swY === $this->swY && $neX === $this->neX && $neY === $this->neY) {
            return $this;
        }

        $that = clone $this;

        $that->swX = $swX;
        $that->swY = $swY;
        $that->neX = $neX;
        $that->neY = $neY;

        return $that;
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
        return $this->srid === null;
    }

    /**
     * Returns the south-west XY point.
     *
     * @throws EmptyGeometryException
     */
    public function getSouthWest() : Point
    {
        if ($this->swX === null || $this->swY === null || $this->srid === null) {
            throw new EmptyGeometryException('The bounding box is empty.');
        }

        return Point::xy($this->swX, $this->swY, $this->srid);
    }

    /**
     * Returns the north-east XY point.
     *
     * @throws EmptyGeometryException
     */
    public function getNorthEast() : Point
    {
        if ($this->neX === null || $this->neY === null || $this->srid === null) {
            throw new EmptyGeometryException('The bounding box is empty.');
        }

        return Point::xy($this->neX, $this->neY, $this->srid);
    }
}
