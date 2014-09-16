<?php

namespace Brick\Geo;

/**
 * @todo this class is not supported yet, but stays here for now as a draft.
 */
class PolyhedralSurface extends Surface implements \Countable, \IteratorAggregate
{
    /**
     * An array of Polygon objects.
     *
     * @var array
     */
    protected $patches = [];

    /**
     * Class constructor.
     *
     * Internal use only, consumer code must use factory() instead.
     *
     * @param array $patches An array on Polygon objects.
     *
     * @throws GeometryException
     */
    protected function __construct(array $patches)
    {
        if (count($patches) == 0) {
            throw new GeometryException('A PolyhedralSurface must be constructed with at least one patch');
        }

        foreach ($patches as $patch) {
            $this->addPatch($patch);
        }
    }

    /**
     * Internal function for the constructor, to provide strong typing.
     *
     * @param Polygon $patch
     */
    private function addPatch(Polygon $patch)
    {
        $this->patches[] = $patch;
    }

    /**
     * Factory method to create a new PolyhedralSurface.
     *
     * @param Polygon[] $polygons
     *
     * @return PolyhedralSurface
     */
    public static function factory(array $polygons)
    {
        return new PolyhedralSurface($polygons);
    }

    /**
     * @return integer
     */
    public function numPatches()
    {
        return count($this->patches);
    }

    /**
     * Returns the specified patch N in this PolyhedralSurface.
     *
     * The patch number is 1-based.
     *
     * @param integer $n
     *
     * @return Polygon
     *
     * @throws GeometryException
     */
    public function patchN($n)
    {
        if (! is_int($n)) {
            throw new GeometryException('The patch number must be an integer');
        }

        // decrement the index, as our array is 0-based
        $n--;

        if ($n < 0 || $n >= count($this->patches)) {
            throw new GeometryException('Patch number out of range');
        }

        return $this->patches[$n];
    }

    /**
     * @todo needs implementation
     *
     * @param Polygon $p
     *
     * @return MultiPolygon
     *
     * @throws GeometryException
     */
    public function boundingPolygons(Polygon $p)
    {
        throw GeometryException::unimplementedMethod(__METHOD__);
    }

    /**
     * @todo needs implementation
     *
     * @return boolean
     *
     * @throws GeometryException
     */
    public function isClosed()
    {
        throw GeometryException::unimplementedMethod(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function centroid()
    {
        return self::getService()->centroid($this);
    }

    /**
     * {@inheritdoc}
     */
    public function pointOnSurface()
    {
        return $this->patches[0]->pointOnSurface();
    }

    /**
     * {@inheritdoc}
     */
    public function area()
    {
        return self::getService()->area($this);
    }

    /**
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'PolyhedralSurface';
    }

    /**
     * {@inheritdoc}
     */
    public function dimension()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return false;
    }

    /**
     * Returns the total number of rings in this Polygon (exterior + interior).
     *
     * Required by interface Countable.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->patches);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->patches);
    }
}
