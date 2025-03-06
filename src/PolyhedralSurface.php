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
 * A PolyhedralSurface is a contiguous collection of polygons, which share common boundary segments.
 *
 * For each pair of polygons that "touch", the common boundary shall be expressible as a finite collection
 * of LineStrings. Each such LineString shall be part of the boundary of at most 2 Polygon patches.
 *
 * For any two polygons that share a common boundary, the "top" of the polygon shall be consistent. This means
 * that when two linear rings from these two Polygons traverse the common boundary segment, they do so in
 * opposite directions. Since the Polyhedral surface is contiguous, all polygons will be thus consistently oriented.
 * This means that a non-oriented surface (such as Möbius band) shall not have single surface representations.
 * They may be represented by a MultiSurface.
 *
 * If each such LineString is the boundary of exactly 2 Polygon patches, then the PolyhedralSurface is a simple,
 * closed polyhedron and is topologically isomorphic to the surface of a sphere. By the Jordan Surface Theorem
 * (Jordan’s Theorem for 2-spheres), such polyhedrons enclose a solid topologically isomorphic to the interior of a
 * sphere; the ball. In this case, the "top" of the surface will either point inward or outward of the enclosed
 * finite solid. If outward, the surface is the exterior boundary of the enclosed surface. If inward, the surface
 * is the interior of the infinite complement of the enclosed solid. A Ball with some number of voids (holes) inside
 * can thus be presented as one exterior boundary shell, and some number in interior boundary shells.
 *
 * @template T of Polygon
 * @template-implements \IteratorAggregate<int<0, max>, T>
 */
readonly class PolyhedralSurface extends Surface implements \Countable, \IteratorAggregate
{
    /**
     * The polygons that compose this PolyhedralSurface.
     *
     * An empty PolyhedralSurface contains no polygons.
     *
     * @var list<T>
     */
    public array $patches;

    /**
     * The coordinate system of each of the patches must match the one of the PolyhedralSurface.
     *
     * @param CoordinateSystem $cs         The coordinate system of the PolyhedralSurface.
     * @param T                ...$patches The patches that compose the PolyhedralSurface.
     *
     * @throws CoordinateSystemException If different coordinate systems are used.
     */
    final public function __construct(CoordinateSystem $cs, Polygon ...$patches)
    {
        $isEmpty = (count($patches) === 0);
        parent::__construct($cs, $isEmpty);

        $this->patches = array_values($patches);

        if ($isEmpty) {
            return;
        }

        CoordinateSystem::check($this, ...$patches);

        $patchType = $this->patchType();

        foreach ($patches as $patch) {
            /**
             * @psalm-suppress DocblockTypeContradiction We do want to enforce this in code, as not everyone uses static analysis!
             * @psalm-suppress MixedArgument It looks like due to this check, Psalm considers that $geometry no longer has a type.
             */
            if (! $patch instanceof $patchType) {
                throw new UnexpectedGeometryException(sprintf(
                    '%s expects instance of %s, instance of %s given.',
                    static::class,
                    $patchType,
                    $patch::class
                ));
            }
        }
    }

    /**
     * Creates a non-empty PolyhedralSurface composed of the given patches.
     *
     * @param Polygon    $patch1 The first patch.
     * @param Polygon ...$patchN The subsequent patches, if any.
     *
     * @throws CoordinateSystemException If the patches use different coordinate systems.
     *
     * @psalm-suppress UnsafeGenericInstantiation Not sure how to fix this.
     */
    public static function of(Polygon $patch1, Polygon ...$patchN) : static
    {
        return new static($patch1->coordinateSystem, $patch1, ...$patchN);
    }

    /**
     * Returns the FQCN of the contained patch type.
     *
     * @return class-string<T>
     */
    protected function patchType() : string
    {
        return Polygon::class;
    }

    public function numPatches() : int
    {
        return count($this->patches);
    }

    /**
     * Returns the specified patch N in this PolyhedralSurface.
     *
     * @param int $n The patch number, 1-based.
     *
     * @return T
     *
     * @throws NoSuchGeometryException If there is no patch at this index.
     */
    public function patchN(int $n) : Polygon
    {
        if (! isset($this->patches[$n - 1])) {
            throw new NoSuchGeometryException('There is no patch in this PolyhedralSurface at index ' . $n);
        }

        return $this->patches[$n - 1];
    }

    /**
     * Returns the patches that compose this PolyhedralSurface.
     *
     * @return list<T>
     *
     * @deprecated Use $patches property instead.
     */
    public function patches() : array
    {
        return $this->patches;
    }

    #[Override]
    public function geometryType() : string
    {
        return 'PolyhedralSurface';
    }

    #[Override]
    public function geometryTypeBinary() : int
    {
        return Geometry::POLYHEDRALSURFACE;
    }

    #[Override]
    public function getBoundingBox() : BoundingBox
    {
        return array_reduce(
            $this->patches,
            fn (BoundingBox $boundingBox, Polygon $patch) => $boundingBox->extendedWithBoundingBox($patch->getBoundingBox()),
            BoundingBox::new(),
        );
    }

    /**
     * @return list<list<list<list<float>>>>
     */
    #[Override]
    public function toArray() : array
    {
        return array_map(
            fn (Polygon $patch) => $patch->toArray(),
            $this->patches,
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
                fn (Polygon $patch) => $patch->project($projector),
                $this->patches,
            ),
        );
    }

    /**
     * Returns the number of patches in this PolyhedralSurface.
     */
    #[Override]
    public function count() : int
    {
        return count($this->patches);
    }

    /**
     * Returns an iterator for the patches in this PolyhedralSurface.
     *
     * @return ArrayIterator<int<0, max>, T>
     */
    #[Override]
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->patches);
    }

    /**
     * Returns a copy of this PolyhedralSurface, with the given patches added.
     *
     * @psalm-suppress UnsafeGenericInstantiation Not sure how to fix this.
     */
    public function withAddedPatches(Polygon ...$patches) : static
    {
        return new static($this->coordinateSystem, ...$this->patches, ...$patches);
    }
}
