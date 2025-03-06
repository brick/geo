<?php

declare(strict_types=1);

namespace Brick\Geo;

use ArrayIterator;
use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\NoSuchGeometryException;
use Brick\Geo\Exception\UnexpectedGeometryException;
use Brick\Geo\Projector\Projector;
use Override;

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
 * @template-implements \IteratorAggregate<int<0, max>, T>
 */
readonly class GeometryCollection extends Geometry implements \Countable, \IteratorAggregate
{
    /**
     * The geometries that compose this GeometryCollection.
     *
     * This array can be empty.
     *
     * @var list<T>
     */
    protected array $geometries;

    /**
     * @param T ...$geometries
     *
     * @throws CoordinateSystemException   If different coordinate systems are used.
     * @throws UnexpectedGeometryException If a geometry is not a valid type for a subclass of GeometryCollection.
     */
    final public function __construct(CoordinateSystem $cs, Geometry ...$geometries)
    {
        $isEmpty = true;

        foreach ($geometries as $geometry) {
            if (! $geometry->isEmpty()) {
                $isEmpty = false;
                break;
            }
        }

        parent::__construct($cs, $isEmpty);

        $this->geometries = array_values($geometries);

        if (! $geometries) {
            return;
        }

        CoordinateSystem::check($this, ...$geometries);

        $containedGeometryType = $this->containedGeometryType();

        foreach ($geometries as $geometry) {
            /**
             * @psalm-suppress DocblockTypeContradiction We do want to enforce this in code, as not everyone uses static analysis!
             * @psalm-suppress MixedArgument It looks like due to this check, Psalm considers that $geometry no longer has a type.
             */
            if (! $geometry instanceof $containedGeometryType) {
                throw new UnexpectedGeometryException(sprintf(
                    '%s expects instance of %s, instance of %s given.',
                    static::class,
                    $containedGeometryType,
                    $geometry::class
                ));
            }
        }
    }

    /**
     * Creates a non-empty GeometryCollection composed of the given geometries.
     *
     * @param Geometry    $geometry1 The first geometry.
     * @param Geometry ...$geometryN The subsequent geometries, if any.
     *
     * @throws CoordinateSystemException   If the geometries use different coordinate systems.
     * @throws UnexpectedGeometryException If a geometry is not a valid type for a subclass of GeometryCollection.
     *
     * @psalm-suppress UnsafeGenericInstantiation Not sure how to fix this.
     */
    public static function of(Geometry $geometry1, Geometry ...$geometryN) : static
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
     * @return T
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
     * @return list<T>
     */
    public function geometries() : array
    {
        return $this->geometries;
    }

    #[Override]
    public function geometryType() : string
    {
        return 'GeometryCollection';
    }

    #[Override]
    public function geometryTypeBinary() : int
    {
        return Geometry::GEOMETRYCOLLECTION;
    }

    #[Override]
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

    #[Override]
    public function getBoundingBox() : BoundingBox
    {
        $boundingBox = BoundingBox::new();

        foreach ($this->geometries as $geometry) {
            $boundingBox = $boundingBox->extendedWithBoundingBox($geometry->getBoundingBox());
        }

        return $boundingBox;
    }

    #[Override]
    public function toArray() : array
    {
        return array_map(
            fn (Geometry $geometry) => $geometry->toArray(),
            $this->geometries,
        );
    }

    /**
     * @psalm-suppress UnsafeGenericInstantiation Not sure how to fix this.
     */
    #[Override]
    public function project(Projector $projector): static
    {
        return new static(
            $projector->getTargetCoordinateSystem($this->coordinateSystem),
            ...array_map(
                fn (Geometry $geometry) => $geometry->project($projector),
                $this->geometries,
            ),
        );
    }

    /**
     * Returns the number of geometries in this GeometryCollection.
     */
    #[Override]
    public function count() : int
    {
        return count($this->geometries);
    }

    /**
     * Returns an iterator for the geometries in this GeometryCollection.
     *
     * @return ArrayIterator<int<0, max>, T>
     */
    #[Override]
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->geometries);
    }

    /**
     * Returns the FQCN of the contained Geometry type.
     *
     * @return class-string<T>
     */
    protected function containedGeometryType() : string
    {
        return Geometry::class;
    }

    /**
     * Returns a copy of this GeometryCollection, with the given geometries added.
     *
     * @param T ...$geometries
     *
     * @psalm-suppress UnsafeGenericInstantiation Not sure how to fix this.
     */
    public function withAddedGeometries(Geometry ...$geometries): static
    {
        return new static($this->coordinateSystem, ...$this->geometries, ...$geometries);
    }
}
