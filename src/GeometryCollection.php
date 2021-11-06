<?php

declare(strict_types=1);

namespace Brick\Geo;

use ArrayIterator;
use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\NoSuchGeometryException;
use Brick\Geo\Exception\UnexpectedGeometryException;

/**
 * A GeometryCollection is a geometric object that is a collection of some number of geometric objects.
 *
 * All the elements in a GeometryCollection shall be in the same Spatial Reference System. This is also the Spatial
 * Reference System for the GeometryCollection.
 *
 * GeometryCollection places no other constraints on its elements. Subclasses of GeometryCollection may restrict
 * membership based on dimension and may also place other constraints on the degree of spatial overlap between
 * elements.
 *
 * By the nature of digital representations, collections are inherently ordered by the underlying storage mechanism.
 * Two collections whose difference is only this order are spatially equal and will return equivalent results in any
 * geometric-defined operations.
 *
 * @template T of Geometry
 */
class GeometryCollection extends Geometry
{
    /**
     * The geometries that compose this GeometryCollection.
     *
     * This array can be empty.
     *
     * @psalm-var list<T>
     *
     * @var Geometry[]
     */
    protected array $geometries = [];

    /**
     * @psalm-param T ...$geometries
     *
     * @throws CoordinateSystemException   If different coordinate systems are used.
     * @throws UnexpectedGeometryException If a geometry is not a valid type for a sub-class of GeometryCollection.
     */
    public function __construct(CoordinateSystem $cs, Geometry ...$geometries)
    {
        $isEmpty = true;

        foreach ($geometries as $geometry) {
            if (! $geometry->isEmpty()) {
                $isEmpty = false;
                break;
            }
        }

        parent::__construct($cs, $isEmpty);

        if (! $geometries) {
            return;
        }

        CoordinateSystem::check($this, ...$geometries);

        $containedGeometryType = $this->containedGeometryType();

        foreach ($geometries as $geometry) {
            if (! $geometry instanceof $containedGeometryType) {
                throw new UnexpectedGeometryException(sprintf(
                    '%s expects instance of %s, instance of %s given.',
                    static::class,
                    $containedGeometryType,
                    get_class($geometry)
                ));
            }
        }

        $this->geometries = $geometries;
    }

    /**
     * Creates a non-empty GeometryCollection composed of the given geometries.
     *
     * @psalm-suppress UnsafeInstantiation
     *
     * @param Geometry    $geometry1 The first geometry.
     * @param Geometry ...$geometryN The subsequent geometries, if any.
     *
     * @return static
     *
     * @throws CoordinateSystemException   If the geometries use different coordinate systems.
     * @throws UnexpectedGeometryException If a geometry is not a valid type for a sub-class of GeometryCollection.
     */
    public static function of(Geometry $geometry1, Geometry ...$geometryN) : GeometryCollection
    {
        return new static($geometry1->coordinateSystem(), $geometry1, ...$geometryN);
    }

    /**
     * Returns the number of geometries in this GeometryCollection.
     */
    public function numGeometries() : int
    {
        return count($this->geometries);
    }

    /**
     * Returns the specified geometry N in this GeometryCollection.
     *
     * @param int $n The geometry number, 1-based.
     *
     * @throws NoSuchGeometryException If there is no Geometry at this index.
     */
    public function geometryN(int $n) : Geometry
    {
        if (! isset($this->geometries[$n - 1])) {
            throw new NoSuchGeometryException('There is no Geometry in this GeometryCollection at index ' . $n);
        }

        return $this->geometries[$n - 1];
    }

    /**
     * Returns the geometries that compose this GeometryCollection.
     *
     * @return Geometry[]
     */
    public function geometries() : array
    {
        return $this->geometries;
    }

    /**
     * @noproxy
     */
    public function geometryType() : string
    {
        return 'GeometryCollection';
    }

    /**
     * @noproxy
     */
    public function geometryTypeBinary() : int
    {
        return Geometry::GEOMETRYCOLLECTION;
    }

    public function dimension() : int
    {
        $dimension = 0;

        foreach ($this->geometries as $geometry) {
            $dim = $geometry->dimension();

            if ($dim > $dimension) {
                $dimension = $dim;
            }
        }

        return $dimension;
    }

    public function toXY(): GeometryCollection
    {
        if ($this->coordinateDimension() === 2) {
            return $this;
        }

        $cs = $this->coordinateSystem
            ->withZ(false)
            ->withM(false);

        $geometries = array_map(fn(Geometry $geometry) => $geometry->toXY(), $this->geometries);

        return new GeometryCollection($cs, ...$geometries);
    }

    public function withoutZ(): GeometryCollection
    {
        if (! $this->coordinateSystem->hasZ()) {
            return $this;
        }

        $cs = $this->coordinateSystem->withZ(false);

        $geometries = array_map(fn(Geometry $geometry) => $geometry->withoutZ(), $this->geometries);

        return new GeometryCollection($cs, ...$geometries);
    }

    public function withoutM(): GeometryCollection
    {
        if (! $this->coordinateSystem->hasM()) {
            return $this;
        }

        $cs = $this->coordinateSystem->withM(false);

        $geometries = array_map(fn(Geometry $geometry) => $geometry->withoutM(), $this->geometries);

        return new GeometryCollection($cs, ...$geometries);
    }

    public function getBoundingBox() : BoundingBox
    {
        $boundingBox = new BoundingBox();

        foreach ($this->geometries as $geometry) {
            $boundingBox = $boundingBox->extendedWithBoundingBox($geometry->getBoundingBox());
        }

        return $boundingBox;
    }

    public function toArray() : array
    {
        $result = [];

        foreach ($this->geometries as $geometry) {
            $result[] = $geometry->toArray();
        }

        return $result;
    }

    public function swapXY() : Geometry
    {
        $that = clone $this;

        foreach ($that->geometries as & $geometry) {
            $geometry = $geometry->swapXY();
        }

        return $that;
    }

    /**
     * Returns the number of geometries in this GeometryCollection.
     *
     * Required by interface Countable.
     */
    public function count() : int
    {
        return count($this->geometries);
    }

    /**
     * Returns an iterator for the geometries in this GeometryCollection.
     *
     * Required by interface IteratorAggregate.
     *
     * @psalm-return ArrayIterator<int, T>
     */
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->geometries);
    }

    /**
     * Returns the FQCN of the contained Geometry type.
     */
    protected function containedGeometryType() : string
    {
        return Geometry::class;
    }
}
