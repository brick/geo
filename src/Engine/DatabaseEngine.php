<?php

namespace Brick\Geo\Engine;

use Brick\Geo\Geometry;
use Brick\Geo\Exception\GeometryException;
use Brick\Geo\Exception\GeometryEngineException;

/**
 * Database implementation of the GeometryEngine.
 *
 * The target database must have support for GIS functions.
 */
abstract class DatabaseEngine implements GeometryEngine
{
    /**
     * Builds a SQL query for a GIS function.
     *
     * @param string  $function        The SQL GIS function to execute.
     * @param array   $parameters      The Geometry objects or scalar values to pass as parameters.
     * @param boolean $returnsGeometry Whether the GIS function returns a Geometry.
     *
     * @return string
     */
    private function buildQuery($function, array $parameters, $returnsGeometry)
    {
        foreach ($parameters as & $parameter) {
            if ($parameter instanceof Geometry) {
                $parameter = 'ST_GeomFromWkb(?, ?)';
            } else {
                $parameter = '?';
            }
        }

        $parameters = implode(', ', $parameters);
        $query = sprintf('%s(%s)', $function, $parameters);

        if ($returnsGeometry) {
            $query = sprintf('ST_AsBinary(%s)', $query);
        }

        return sprintf('SELECT %s', $query);
    }

    /**
     * Executes a SQL query.
     *
     * @param string $query      The SQL query to execute.
     * @param array  $parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @return mixed
     *
     * @throws GeometryEngineException
     */
    abstract protected function executeQuery($query, array $parameters);

    /**
     * Builds and executes a SQL query for a GIS function.
     *
     * @param string  $function        The SQL GIS function to execute.
     * @param array   $parameters      The Geometry objects or scalar values to pass as parameters.
     * @param boolean $returnsGeometry Whether the GIS function returns a Geometry.
     *
     * @return mixed
     *
     * @throws GeometryEngineException
     */
    private function query($function, array $parameters, $returnsGeometry)
    {
        $query = $this->buildQuery($function, $parameters, $returnsGeometry);
        $result = $this->executeQuery($query, $parameters);

        if ($result === null) {
            throw GeometryEngineException::operationYieldedNoResult();
        }

        if (is_resource($result)) {
            $result = stream_get_contents($result);
        }

        return $result;
    }

    /**
     * Executes a SQL query returning a boolean value.
     *
     * @param string $function   The SQL GIS function to execute.
     * @param array  $parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @return boolean
     *
     * @throws GeometryEngineException
     */
    private function queryBoolean($function, array $parameters)
    {
        $result = $this->query($function, $parameters, false);

        return (boolean) $result;
    }

    /**
     * Executes a SQL query returning a floating point value.
     *
     * @param string $function   The SQL GIS function to execute.
     * @param array  $parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @return float
     *
     * @throws GeometryEngineException
     */
    private function queryFloat($function, array $parameters)
    {
        $result = $this->query($function, $parameters, false);

        return (float) $result;
    }

    /**
     * Executes a SQL query returning a Geometry object.
     *
     * @param string $function   The SQL GIS function to execute.
     * @param array  $parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @return Geometry
     *
     * @throws GeometryEngineException
     */
    private function queryGeometry($function, array $parameters)
    {
        $result = $this->query($function, $parameters, true);

        return Geometry::fromBinary($result);
    }

    /**
     * {@inheritdoc}
     */
    public function contains(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Contains', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function intersects(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Intersects', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function union(Geometry $a, Geometry $b)
    {
        return $this->queryGeometry('ST_Union', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function intersection(Geometry $a, Geometry $b)
    {
        return $this->queryGeometry('ST_Intersection', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function difference(Geometry $a, Geometry $b)
    {
        return $this->queryGeometry('ST_Difference', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function envelope(Geometry $g)
    {
        return $this->queryGeometry('ST_Envelope', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function centroid(Geometry $g)
    {
        return $this->queryGeometry('ST_Centroid', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function pointOnSurface(Geometry $g)
    {
        return $this->queryGeometry('ST_PointOnSurface', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function length(Geometry $g)
    {
        return $this->queryFloat('ST_Length', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function area(Geometry $g)
    {
        return $this->queryFloat('ST_Area', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function boundary(Geometry $g)
    {
        return $this->queryGeometry('ST_Boundary', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(Geometry $g)
    {
        return $this->queryBoolean('ST_IsValid', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed(Geometry $g)
    {
        return $this->queryBoolean('ST_IsClosed', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function isSimple(Geometry $g)
    {
        return $this->queryBoolean('ST_IsSimple', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function equals(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Equals', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function disjoint(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Disjoint', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function touches(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Touches', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function crosses(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Crosses', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function within(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Within', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function overlaps(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Overlaps', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function relate(Geometry $a, Geometry $b, $matrix)
    {
        return $this->queryBoolean('ST_Relate', [$a, $b, $matrix]);
    }

    /**
     * {@inheritdoc}
     */
    public function locateAlong(Geometry $g, $mValue)
    {
        return $this->queryGeometry('ST_LocateAlong', [$g, $mValue]);
    }

    /**
     * {@inheritdoc}
     */
    public function locateBetween(Geometry $g, $mStart, $mEnd)
    {
        return $this->queryGeometry('ST_LocateBetween', [$g, $mStart, $mEnd]);
    }

    /**
     * {@inheritdoc}
     */
    public function distance(Geometry $a, Geometry $b)
    {
        return $this->queryFloat('ST_Distance', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function buffer(Geometry $g, $distance)
    {
        return $this->queryGeometry('ST_Buffer', [$g, $distance]);
    }

    /**
     * {@inheritdoc}
     */
    public function convexHull(Geometry $g)
    {
        return $this->queryGeometry('ST_ConvexHull', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function symDifference(Geometry $a, Geometry $b)
    {
        return $this->queryGeometry('ST_SymDifference', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function snapToGrid(Geometry $g, $size)
    {
        return $this->queryGeometry('ST_SnapToGrid', [$g, $size]);
    }

    /**
     * {@inheritdoc}
     */
    public function simplify(Geometry $g, $tolerance)
    {
        return $this->queryGeometry('ST_Simplify', [$g, $tolerance]);
    }

    /**
     * {@inheritdoc}
     */
    public function maxDistance(Geometry $a, Geometry $b)
    {
        return $this->queryFloat('ST_MaxDistance', [$a, $b]);
    }
}
