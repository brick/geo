<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;

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
class GeometryCollection extends Geometry implements \Countable, \IteratorAggregate
{
    /**
     * An array of Geometry objects in the collection.
     *
     * @var Geometry[]
     */
    protected $geometries = [];

    /**
     * @param Geometry[] $geometries
     * @param integer    $srid
     *
     * @return static
     */
    public static function xy(array $geometries, $srid = 0)
    {
        return self::create($geometries, false, false, $srid);
    }

    /**
     * @param Geometry[] $geometries
     * @param integer    $srid
     *
     * @return static
     */
    public static function xyz(array $geometries, $srid = 0)
    {
        return self::create($geometries, true, false, $srid);
    }

    /**
     * @param Geometry[] $geometries
     * @param integer    $srid
     *
     * @return static
     */
    public static function xym(array $geometries, $srid = 0)
    {
        return self::create($geometries, false, true, $srid);
    }

    /**
     * @param Geometry[] $geometries
     * @param integer    $srid
     *
     * @return static
     */
    public static function xyzm(array $geometries, $srid = 0)
    {
        return self::create($geometries, true, true, $srid);
    }

    /**
     * @param Geometry[] $geometries
     * @param boolean    $is3D
     * @param boolean    $isMeasured
     * @param integer    $srid
     *
     * @return static
     *
     * @throws GeometryException
     */
    public static function create(array $geometries, $is3D, $isMeasured, $srid)
    {
        $is3D       = (bool) $is3D;
        $isMeasured = (bool) $isMeasured;

        $srid = (int) $srid;

        self::checkGeometries($geometries, static::containedGeometryType(), $is3D, $isMeasured, $srid);

        $geometryCollection = new static(self::checkEmpty($geometries), $is3D, $isMeasured, $srid);
        $geometryCollection->geometries = array_values($geometries);

        return $geometryCollection;
    }

    /**
     * @deprecated Use a factory method that explictly specifies the dimensionality and SRID.
     *
     * @param array $geometries An array of Geometry objects.
     *
     * @return static
     *
     * @throws GeometryException If the array contains objects not of the current type.
     */
    public static function factory(array $geometries)
    {
        $geometryType = static::containedGeometryType();

        foreach ($geometries as $geometry) {
            if (! $geometry instanceof $geometryType) {
                throw GeometryException::unexpectedGeometryType($geometryType, $geometry);
            }
        }

        self::getDimensions($geometries, $is3D, $isMeasured, $srid);

        $geometryCollection = new static(self::checkEmpty($geometries), $is3D, $isMeasured, $srid);
        $geometryCollection->geometries = array_values($geometries);

        return $geometryCollection;
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
     * Returns the Nth geometry in this GeometryCollection.
     *
     * The geometry number is 1-based.
     *
     * @param integer $n
     *
     * @return Geometry
     *
     * @throws GeometryException If there is no Geometry at this index.
     */
    public function geometryN($n)
    {
        $n = (int) $n;

        if (! isset($this->geometries[$n - 1])) {
            throw new GeometryException('There is no Geometry in this GeometryCollection at index ' . $n);
        }

        return $this->geometries[$n - 1];
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

    /**
     * Returns the FQCN of the contained Geometry type.
     *
     * @return string
     */
    protected static function containedGeometryType()
    {
        return Geometry::class;
    }

    /**
     * @param Geometry[] $geometries
     *
     * @return boolean
     */
    private static function checkEmpty(array $geometries)
    {
        foreach ($geometries as $geometry) {
            if (! $geometry->isEmpty()) {
                return false;
            }
        }

        return true;
    }
}
