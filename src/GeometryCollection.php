<?php

namespace Brick\Geo;

/**
 * A GeometryCollection is a geometry that is a collection of one or more geometries of any class.
 */
class GeometryCollection extends Geometry implements \Countable, \IteratorAggregate
{
    /**
     * An array of Geometry objects in the collection
     *
     * @var Geometry[]
     */
    protected $geometries = [];

    /**
     * Class constructor.
     *
     * Internal use only, consumer code must use factory() instead.
     *
     * @param array $geometries An array of Geometry objects
     */
    protected function __construct(array $geometries)
    {
        foreach ($geometries as $geometry) {
            $this->addGeometry($geometry);
        }
    }

    /**
     * Internal function for the constructor, to provide strong typing.
     *
     * @param Geometry $geometry
     *
     * @return void
     */
    private function addGeometry(Geometry $geometry)
    {
        $this->geometries[] = $geometry;
    }

    /**
     * @param array $geometries An array of Geometry objects.
     *
     * @return GeometryCollection
     */
    public static function factory(array $geometries)
    {
        return new GeometryCollection($geometries);
    }

    /**
     * Returns the number of geometries in this GeometryCollection.
     *
     * @return integer
     */
    public function numGeometries()
    {
        return $this->count($this->geometries);
    }

    /**
     * Returns the Nth geometry in this GeometryCollection.
     *
     * The geometry number is 1-based.
     *
     * @param integer $n
     *
     * @return Geometry
     *
     * @throws GeometryException
     */
    public function geometryN($n)
    {
        if (! is_int($n)) {
            throw new GeometryException('The geometry number must be an integer');
        }

        // decrement the index, as our array is 0-based
        $n--;

        if ($n < 0 || $n >= count($this->geometries)) {
            throw new GeometryException('Geometry number out of range');
        }

        return $this->geometries[$n];
    }

    /**
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'GeometryCollection';
    }

    /**
     * {@inheritdoc}
     *
     * Returns the largest dimension of the geometries of the collection.
     */
    public function dimension()
    {
        $dimension = -1;

        foreach ($this->geometries as $geometry) {
            $dimension = max($dimension, $geometry->dimension());
        }

        return $dimension;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        foreach ($this->geometries as $geometry) {
            if (! $geometry->isEmpty()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the total number of geometries in this GeometryCollection.
     *
     * Required by interface Countable.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->geometries);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->geometries);
    }
}
