<?php

namespace Brick\Geo\Engine;

use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Geometry;
use Brick\Geo\Proxy;

/**
 * Database implementation of the GeometryEngine.
 *
 * The target database must have support for GIS functions.
 */
abstract class DatabaseEngine implements GeometryEngine
{
    /**
     * @var bool
     */
    protected $useProxy;

    /**
     * Builds a SQL query for a GIS function.
     *
     * @param string $function        The SQL GIS function to execute.
     * @param array  $parameters      The Geometry objects or scalar values to pass as parameters.
     * @param bool   $returnsGeometry Whether the GIS function returns a Geometry.
     *
     * @return string
     */
    private function buildQuery($function, array $parameters, $returnsGeometry)
    {
        foreach ($parameters as & $parameter) {
            if ($parameter instanceof Geometry) {
                if ($parameter->isEmpty()) {
                    $parameter = 'ST_GeomFromText(?, ?)';
                } else {
                    $parameter = 'ST_GeomFromWKB(?, ?)';
                }
            } else {
                $parameter = '?';
            }
        }

        $parameters = implode(', ', $parameters);
        $query = sprintf('SELECT %s(%s)', $function, $parameters);

        if (! $returnsGeometry) {
            return $query;
        }

        return sprintf('
            SELECT
                CASE WHEN ST_IsEmpty(g) THEN ST_AsText(g) ELSE NULL END,
                CASE WHEN ST_IsEmpty(g) THEN NULL ELSE ST_AsBinary(g) END,
                ST_GeometryType(g),
                ST_SRID(g)
            FROM (%s AS g) AS q
        ', $query);
    }

    /**
     * Executes a SQL query.
     *
     * @param string $query      The SQL query to execute.
     * @param array  $parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @return array A numeric result array.
     *
     * @throws GeometryEngineException
     */
    abstract protected function executeQuery($query, array $parameters);

    /**
     * Builds and executes a SQL query for a GIS function.
     *
     * @param string $function        The SQL GIS function to execute.
     * @param array  $parameters      The Geometry objects or scalar values to pass as parameters.
     * @param bool   $returnsGeometry Whether the GIS function returns a Geometry.
     *
     * @return array A numeric result array.
     *
     * @throws GeometryEngineException
     */
    private function query($function, array $parameters, $returnsGeometry)
    {
        $query = $this->buildQuery($function, $parameters, $returnsGeometry);
        $result = $this->executeQuery($query, $parameters);

        return $result;
    }

    /**
     * Queries a GIS function returning a boolean value.
     *
     * @param string   $function   The SQL GIS function to execute.
     * @param mixed ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @return bool
     *
     * @throws GeometryEngineException
     */
    private function queryBoolean($function, ...$parameters)
    {
        list ($result) = $this->query($function, $parameters, false);

        if ($result === null || $result === -1) { // SQLite3 returns -1 when calling a boolean GIS function on a NULL result.
            throw GeometryEngineException::operationYieldedNoResult();
        }

        return (bool) $result;
    }

    /**
     * Queries a GIS function returning a floating point value.
     *
     * @param string   $function   The SQL GIS function to execute.
     * @param mixed ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @return float
     *
     * @throws GeometryEngineException
     */
    private function queryFloat($function, ...$parameters)
    {
        list ($result) = $this->query($function, $parameters, false);

        if ($result === null) {
            throw GeometryEngineException::operationYieldedNoResult();
        }

        return (float) $result;
    }

    /**
     * Queries a GIS function returning a Geometry object.
     *
     * @param string   $function   The SQL GIS function to execute.
     * @param mixed ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @return Geometry
     *
     * @throws GeometryEngineException
     */
    private function queryGeometry($function, ...$parameters)
    {
        list ($wkt, $wkb, $geometryType, $srid) = $this->query($function, $parameters, true);

        if ($wkt !== null) {
            if ($this->useProxy) {
                $proxyClassName = $this->getProxyClassName($geometryType);

                return new $proxyClassName($wkt, false, $srid);
            }

            return Geometry::fromText($wkt, $srid);
        }

        if ($wkb !== null) {
            if (is_resource($wkb)) {
                $wkb = stream_get_contents($wkb);
            }

            if ($this->useProxy) {
                $proxyClassName = $this->getProxyClassName($geometryType);

                return new $proxyClassName($wkb, true, $srid);
            }

            return Geometry::fromBinary($wkb, $srid);
        }

        throw GeometryEngineException::operationYieldedNoResult();
    }

    /**
     * @param string $geometryType
     *
     * @return string
     *
     * @throws GeometryEngineException
     */
    private function getProxyClassName($geometryType)
    {
        $proxyClasses = [
            'CIRCULARSTRING'     => Proxy\CircularStringProxy::class,
            'COMPOUNDCURVE'      => Proxy\CompoundCurveProxy::class,
            'CURVE'              => Proxy\CurveProxy::class,
            'CURVEPOLYGON'       => Proxy\CurvePolygonProxy::class,
            'GEOMETRY'           => Proxy\GeometryProxy::class,
            'GEOMETRYCOLLECTION' => Proxy\GeometryCollectionProxy::class,
            'LINESTRING'         => Proxy\LineStringProxy::class,
            'MULTICURVE'         => Proxy\MultiCurveProxy::class,
            'MULTILINESTRING'    => Proxy\MultiLineStringProxy::class,
            'MULTIPOINT'         => Proxy\MultiPointProxy::class,
            'MULTIPOLYGON'       => Proxy\MultiPolygonProxy::class,
            'MULTISURFACE'       => Proxy\MultiSurfaceProxy::class,
            'POINT'              => Proxy\PointProxy::class,
            'POLYGON'            => Proxy\PolygonProxy::class,
            'POLYHEDRALSURFACE'  => Proxy\PolyhedralSurfaceProxy::class,
            'SURFACE'            => Proxy\SurfaceProxy::class,
            'TIN'                => Proxy\TINProxy::class,
            'TRIANGLE'           => Proxy\TriangleProxy::class
        ];

        $geometryType = strtoupper($geometryType);
        $geometryType = preg_replace('/^ST_/', '', $geometryType);
        $geometryType = preg_replace('/ .*/', '', $geometryType);

        if (! isset($proxyClasses[$geometryType])) {
            throw new GeometryEngineException('Unknown geometry type: ' . $geometryType);
        }

        return $proxyClasses[$geometryType];
    }

    /**
     * {@inheritdoc}
     */
    public function contains(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Contains', $a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function intersects(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Intersects', $a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function union(Geometry $a, Geometry $b)
    {
        return $this->queryGeometry('ST_Union', $a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function intersection(Geometry $a, Geometry $b)
    {
        return $this->queryGeometry('ST_Intersection', $a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function difference(Geometry $a, Geometry $b)
    {
        return $this->queryGeometry('ST_Difference', $a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function envelope(Geometry $g)
    {
        return $this->queryGeometry('ST_Envelope', $g);
    }

    /**
     * {@inheritdoc}
     */
    public function centroid(Geometry $g)
    {
        return $this->queryGeometry('ST_Centroid', $g);
    }

    /**
     * {@inheritdoc}
     */
    public function pointOnSurface(Geometry $g)
    {
        return $this->queryGeometry('ST_PointOnSurface', $g);
    }

    /**
     * {@inheritdoc}
     */
    public function length(Geometry $g)
    {
        return $this->queryFloat('ST_Length', $g);
    }

    /**
     * {@inheritdoc}
     */
    public function area(Geometry $g)
    {
        return $this->queryFloat('ST_Area', $g);
    }

    /**
     * {@inheritdoc}
     */
    public function boundary(Geometry $g)
    {
        return $this->queryGeometry('ST_Boundary', $g);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(Geometry $g)
    {
        return $this->queryBoolean('ST_IsValid', $g);
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed(Geometry $g)
    {
        return $this->queryBoolean('ST_IsClosed', $g);
    }

    /**
     * {@inheritdoc}
     */
    public function isSimple(Geometry $g)
    {
        return $this->queryBoolean('ST_IsSimple', $g);
    }

    /**
     * {@inheritdoc}
     */
    public function equals(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Equals', $a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function disjoint(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Disjoint', $a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function touches(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Touches', $a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function crosses(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Crosses', $a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function within(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Within', $a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function overlaps(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Overlaps', $a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function relate(Geometry $a, Geometry $b, $matrix)
    {
        return $this->queryBoolean('ST_Relate', $a, $b, $matrix);
    }

    /**
     * {@inheritdoc}
     */
    public function locateAlong(Geometry $g, $mValue)
    {
        return $this->queryGeometry('ST_LocateAlong', $g, $mValue);
    }

    /**
     * {@inheritdoc}
     */
    public function locateBetween(Geometry $g, $mStart, $mEnd)
    {
        return $this->queryGeometry('ST_LocateBetween', $g, $mStart, $mEnd);
    }

    /**
     * {@inheritdoc}
     */
    public function distance(Geometry $a, Geometry $b)
    {
        return $this->queryFloat('ST_Distance', $a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function buffer(Geometry $g, $distance)
    {
        return $this->queryGeometry('ST_Buffer', $g, $distance);
    }

    /**
     * {@inheritdoc}
     */
    public function convexHull(Geometry $g)
    {
        return $this->queryGeometry('ST_ConvexHull', $g);
    }

    /**
     * {@inheritdoc}
     */
    public function symDifference(Geometry $a, Geometry $b)
    {
        return $this->queryGeometry('ST_SymDifference', $a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function snapToGrid(Geometry $g, $size)
    {
        return $this->queryGeometry('ST_SnapToGrid', $g, $size);
    }

    /**
     * {@inheritdoc}
     */
    public function simplify(Geometry $g, $tolerance)
    {
        return $this->queryGeometry('ST_Simplify', $g, $tolerance);
    }

    /**
     * {@inheritdoc}
     */
    public function maxDistance(Geometry $a, Geometry $b)
    {
        return $this->queryFloat('ST_MaxDistance', $a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function boundingPolygons(Geometry $g)
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }
}
