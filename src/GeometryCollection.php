<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;
use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\NoSuchGeometryException;

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
 */
class GeometryCollection extends Geometry
{
    /**
     * The geometries that compose this GeometryCollection.
     *
     * This array can be empty.
     *
     * @var Geometry[]
     */
    protected $geometries = [];

    /**
     * Class constructor.
     *
     * @param CoordinateSystem $cs
     * @param Geometry         ...$geometries
     *
     * @throws CoordinateSystemException If different coordinate systems are used.
     * @throws GeometryException         If a geometry is not a valid type for a sub-class of GeometryCollection.
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

        $containedGeometryType = $this->containedGeometryType();

        foreach ($geometries as $geometry) {
            $cs->checkMatches($geometry->coordinateSystem());

            if (! $geometry instanceof $containedGeometryType) {
                throw new GeometryException(sprintf(
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
     * Returns a GeometryCollection composed of the given geometries.
     *
     * All geometries must be using the same coordinate system.
     * The coordinate system being inferred from the geometries, an empty geometry list is not allowed.
     * To create an empty GeometryCollection, use the class constructor instead.
     *
     * @param Geometry ...$geometries The geometries that compose the GeometryCollection.
     *
     * @return static
     *
     * @throws GeometryException
     */
    public static function of(Geometry ...$geometries)
    {
        if (! $geometries) {
            throw GeometryException::atLeastOneGeometryExpected(static::class, __FUNCTION__);
        }

        return new static($geometries[0]->coordinateSystem(), ...$geometries);
    }

    /**
     * Returns the number of geometries in this GeometryCollection.
     *
     * @return integer
     */
    public function numGeometries()
    {
        return count($this->geometries);
    }

    /**
     * Returns the specified geometry N in this GeometryCollection.
     *
     * @param integer $n The geometry number, 1-based.
     *
     * @return Geometry
     *
     * @throws NoSuchGeometryException If there is no Geometry at this index.
     */
    public function geometryN($n)
    {
        $n = (int) $n;

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
    public function geometries()
    {
        return $this->geometries;
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'GeometryCollection';
    }

    /**
     * {@inheritdoc}
     */
    public function dimension()
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

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $result = [];

        foreach ($this->geometries as $geometry) {
            $result[] = $geometry->toArray();
        }

        return $result;
    }

    /**
     * Returns the number of geometries in this GeometryCollection.
     *
     * Required by interface Countable.
     *
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->geometries);
    }

    /**
     * Returns an iterator for the geometries in this GeometryCollection.
     *
     * Required by interface IteratorAggregate.
     *
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->geometries);
    }

    /**
     * Returns the FQCN of the contained Geometry type.
     *
     * @return string
     */
    protected function containedGeometryType()
    {
        return Geometry::class;
    }
}
