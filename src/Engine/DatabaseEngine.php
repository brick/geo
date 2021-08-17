<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Geometry;
use Brick\Geo\Point;
use Brick\Geo\Proxy;

/**
 * Database implementation of the GeometryEngine.
 *
 * The target database must have support for GIS functions.
 */
abstract class DatabaseEngine implements GeometryEngine
{
    private bool $useProxy;

    public function __construct(bool $useProxy)
    {
        $this->useProxy = $useProxy;
    }

    /**
     * Executes a SQL query.
     *
     * @psalm-param list<GeometryParameter|scalar|null> $parameters
     *
     * @param string $query      The SQL query to execute.
     * @param array  $parameters The geometry data or scalar values to pass as parameters.
     *
     * @return array A numeric result array.
     *
     * @throws GeometryEngineException
     */
    abstract protected function executeQuery(string $query, array $parameters) : array;

    /**
     * Returns the syntax required to perform a ST_GeomFromText(), together with placeholders.
     *
     * This method may be overridden if necessary.
     */
    protected function getGeomFromTextSyntax(): string
    {
        return 'ST_GeomFromText(?, ?)';
    }

    /**
     * Returns the syntax required to perform a ST_GeomFromWKB(), together with placeholders.
     *
     * This method may be overridden if necessary.
     */
    protected function getGeomFromWKBSyntax(): string
    {
        return 'ST_GeomFromWKB(?, ?)';
    }

    /**
     * Returns the placeholder syntax for the given parameter.
     *
     * This method may be overridden to perform explicit type casts if necessary.
     *
     * @param scalar|null $parameter
     */
    protected function getParameterPlaceholder($parameter): string
    {
        return '?';
    }

    /**
     * Builds and executes a SQL query for a GIS function.
     *
     * @psalm-param list<Geometry|scalar|null> $parameters
     *
     * @param string $function        The SQL GIS function to execute.
     * @param array  $parameters      The Geometry objects or scalar values to pass as parameters.
     * @param bool   $returnsGeometry Whether the GIS function returns a Geometry.
     *
     * @return array A numeric result array.
     *
     * @throws GeometryEngineException
     */
    private function query(string $function, array $parameters, bool $returnsGeometry) : array
    {
        $queryParameters = [];
        $queryValues = [];

        foreach ($parameters as $parameter) {
            if ($parameter instanceof Geometry) {
                if ($parameter instanceof Proxy\ProxyInterface) {
                    $sendAsBinary = $parameter->isProxyBinary();
                } else {
                    $sendAsBinary = ! $parameter->isEmpty();
                }

                $queryParameters[] = $sendAsBinary
                    ? $this->getGeomFromWKBSyntax()
                    : $this->getGeomFromTextSyntax();

                $queryValues[] = new GeometryParameter($parameter, $sendAsBinary);
            } else {
                $queryParameters[] = $this->getParameterPlaceholder($parameter);
                $queryValues[] = $parameter;
            }
        }

        $query = sprintf('SELECT %s(%s)', $function, implode(', ', $queryParameters));

        if ($returnsGeometry) {
            $query = sprintf('
                SELECT
                    CASE WHEN ST_IsEmpty(g) THEN ST_AsText(g) ELSE NULL END,
                    CASE WHEN ST_IsEmpty(g) THEN NULL ELSE ST_AsBinary(g) END,
                    ST_GeometryType(g),
                    ST_SRID(g)
                FROM (%s AS g) AS q
            ', $query);
        }

        return $this->executeQuery($query, $queryValues);
    }

    /**
     * Queries a GIS function returning a boolean value.
     *
     * @psalm-param Geometry|scalar|null ...$parameters
     *
     * @param string   $function   The SQL GIS function to execute.
     * @param mixed ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @throws GeometryEngineException
     */
    private function queryBoolean(string $function, ...$parameters) : bool
    {
        [$result] = $this->query($function, $parameters, false);

        // SQLite3 returns -1 when calling a boolean GIS function on a NULL result,
        // MariaDB returns -1 when an unsupported operation is performed on a Z/M geometry.
        if ($result === null || $result === -1 || $result === '-1') {
            throw GeometryEngineException::operationYieldedNoResult();
        }

        return (bool) $result;
    }

    /**
     * Queries a GIS function returning a floating point value.
     *
     * @psalm-param Geometry|scalar|null ...$parameters
     *
     * @param string   $function   The SQL GIS function to execute.
     * @param mixed ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @throws GeometryEngineException
     */
    private function queryFloat(string $function, ...$parameters) : float
    {
        [$result] = $this->query($function, $parameters, false);

        if ($result === null) {
            throw GeometryEngineException::operationYieldedNoResult();
        }

        return (float) $result;
    }

    /**
     * Queries a GIS function returning a Geometry object.
     *
     * @psalm-param Geometry|scalar|null ...$parameters
     *
     * @param string   $function   The SQL GIS function to execute.
     * @param mixed ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @throws GeometryEngineException
     */
    private function queryGeometry(string $function, ...$parameters) : Geometry
    {
        /** @var array{string|null, string|resource|null, string, int|numeric-string} $result */
        $result = $this->query($function, $parameters, true);

        [$wkt, $wkb, $geometryType, $srid] = $result;

        $srid = (int) $srid;

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
     * @psalm-return class-string<Proxy\ProxyInterface&Geometry>
     *
     * @throws GeometryEngineException
     */
    private function getProxyClassName(string $geometryType) : string
    {
        $proxyClasses = [
            'CIRCULARSTRING'     => Proxy\CircularStringProxy::class,
            'COMPOUNDCURVE'      => Proxy\CompoundCurveProxy::class,
            'CURVE'              => Proxy\CurveProxy::class,
            'CURVEPOLYGON'       => Proxy\CurvePolygonProxy::class,
            'GEOMCOLLECTION'     => Proxy\GeometryCollectionProxy::class, /* MySQL 8 - https://github.com/brick/geo/pull/33 */
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

    public function contains(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBoolean('ST_Contains', $a, $b);
    }

    public function intersects(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBoolean('ST_Intersects', $a, $b);
    }

    public function union(Geometry $a, Geometry $b) : Geometry
    {
        return $this->queryGeometry('ST_Union', $a, $b);
    }

    public function intersection(Geometry $a, Geometry $b) : Geometry
    {
        return $this->queryGeometry('ST_Intersection', $a, $b);
    }

    public function difference(Geometry $a, Geometry $b) : Geometry
    {
        return $this->queryGeometry('ST_Difference', $a, $b);
    }

    public function envelope(Geometry $g) : Geometry
    {
        return $this->queryGeometry('ST_Envelope', $g);
    }

    /**
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     */
    public function centroid(Geometry $g) : Point
    {
        return $this->queryGeometry('ST_Centroid', $g);
    }

    public function pointOnSurface(Geometry $g) : Geometry
    {
        return $this->queryGeometry('ST_PointOnSurface', $g);
    }

    public function length(Geometry $g) : float
    {
        return $this->queryFloat('ST_Length', $g);
    }

    public function area(Geometry $g) : float
    {
        return $this->queryFloat('ST_Area', $g);
    }

    public function azimuth(Point $observer, Point $subject) : float
    {
        return $this->queryFloat('ST_Azimuth', $observer, $subject);
    }

    public function boundary(Geometry $g) : Geometry
    {
        return $this->queryGeometry('ST_Boundary', $g);
    }

    public function isValid(Geometry $g) : bool
    {
        return $this->queryBoolean('ST_IsValid', $g);
    }

    public function isClosed(Geometry $g) : bool
    {
        return $this->queryBoolean('ST_IsClosed', $g);
    }

    public function isSimple(Geometry $g) : bool
    {
        return $this->queryBoolean('ST_IsSimple', $g);
    }

    public function equals(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBoolean('ST_Equals', $a, $b);
    }

    public function disjoint(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBoolean('ST_Disjoint', $a, $b);
    }

    public function touches(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBoolean('ST_Touches', $a, $b);
    }

    public function crosses(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBoolean('ST_Crosses', $a, $b);
    }

    public function within(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBoolean('ST_Within', $a, $b);
    }

    public function overlaps(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBoolean('ST_Overlaps', $a, $b);
    }

    public function relate(Geometry $a, Geometry $b, string $matrix) : bool
    {
        return $this->queryBoolean('ST_Relate', $a, $b, $matrix);
    }

    public function locateAlong(Geometry $g, float $mValue) : Geometry
    {
        return $this->queryGeometry('ST_LocateAlong', $g, $mValue);
    }

    public function locateBetween(Geometry $g, float $mStart, float $mEnd) : Geometry
    {
        return $this->queryGeometry('ST_LocateBetween', $g, $mStart, $mEnd);
    }

    public function distance(Geometry $a, Geometry $b) : float
    {
        return $this->queryFloat('ST_Distance', $a, $b);
    }

    public function buffer(Geometry $g, float $distance) : Geometry
    {
        return $this->queryGeometry('ST_Buffer', $g, $distance);
    }

    public function convexHull(Geometry $g) : Geometry
    {
        return $this->queryGeometry('ST_ConvexHull', $g);
    }

    public function symDifference(Geometry $a, Geometry $b) : Geometry
    {
        return $this->queryGeometry('ST_SymDifference', $a, $b);
    }

    public function snapToGrid(Geometry $g, float $size) : Geometry
    {
        return $this->queryGeometry('ST_SnapToGrid', $g, $size);
    }

    public function simplify(Geometry $g, float $tolerance) : Geometry
    {
        return $this->queryGeometry('ST_Simplify', $g, $tolerance);
    }

    public function maxDistance(Geometry $a, Geometry $b) : float
    {
        return $this->queryFloat('ST_MaxDistance', $a, $b);
    }

    public function boundingPolygons(Geometry $g) : Geometry
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    public function transform(Geometry $g, int $srid) : Geometry
    {
        return $this->queryGeometry('ST_Transform', $g, $srid);
    }
}
