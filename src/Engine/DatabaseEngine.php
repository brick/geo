<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Curve;
use Brick\Geo\Engine\Internal\TypeChecker;
use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Geometry;
use Brick\Geo\LineString;
use Brick\Geo\MultiCurve;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiSurface;
use Brick\Geo\Point;
use Brick\Geo\Proxy;
use Brick\Geo\Surface;
use Override;

use function assert;
use function implode;
use function is_resource;
use function preg_replace;
use function sprintf;
use function stream_get_contents;
use function strtoupper;

/**
 * Database implementation of the GeometryEngine.
 *
 * The target database must have support for GIS functions.
 */
abstract class DatabaseEngine implements GeometryEngine
{
    private readonly bool $useProxy;

    public function __construct(bool $useProxy)
    {
        $this->useProxy = $useProxy;
    }

    #[Override]
    public function contains(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('ST_Contains', $a, $b);
    }

    #[Override]
    public function intersects(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('ST_Intersects', $a, $b);
    }

    #[Override]
    public function union(Geometry $a, Geometry $b): Geometry
    {
        return $this->queryGeometry('ST_Union', $a, $b);
    }

    #[Override]
    public function intersection(Geometry $a, Geometry $b): Geometry
    {
        return $this->queryGeometry('ST_Intersection', $a, $b);
    }

    #[Override]
    public function difference(Geometry $a, Geometry $b): Geometry
    {
        return $this->queryGeometry('ST_Difference', $a, $b);
    }

    #[Override]
    public function envelope(Geometry $g): Geometry
    {
        return $this->queryGeometry('ST_Envelope', $g);
    }

    #[Override]
    public function centroid(Geometry $g): Point
    {
        $centroid = $this->queryGeometry('ST_Centroid', $g);
        TypeChecker::check($centroid, Point::class);

        return $centroid;
    }

    #[Override]
    public function pointOnSurface(Surface|MultiSurface $g): Point
    {
        $pointOnSurface = $this->queryGeometry('ST_PointOnSurface', $g);
        TypeChecker::check($pointOnSurface, Point::class);

        return $pointOnSurface;
    }

    #[Override]
    public function length(Curve|MultiCurve $g): float
    {
        return $this->queryFloat('ST_Length', $g);
    }

    #[Override]
    public function area(Surface|MultiSurface $g): float
    {
        return $this->queryFloat('ST_Area', $g);
    }

    #[Override]
    public function azimuth(Point $observer, Point $subject): float
    {
        return $this->queryFloat('ST_Azimuth', $observer, $subject);
    }

    #[Override]
    public function boundary(Geometry $g): Geometry
    {
        return $this->queryGeometry('ST_Boundary', $g);
    }

    #[Override]
    public function isValid(Geometry $g): bool
    {
        return $this->queryBoolean('ST_IsValid', $g);
    }

    #[Override]
    public function isClosed(Geometry $g): bool
    {
        return $this->queryBoolean('ST_IsClosed', $g);
    }

    #[Override]
    public function isSimple(Geometry $g): bool
    {
        return $this->queryBoolean('ST_IsSimple', $g);
    }

    #[Override]
    public function isRing(Curve $curve): bool
    {
        try {
            return $this->queryBoolean('ST_IsRing', $curve);
        } catch (GeometryEngineException) {
            // Not all RDBMS (hello, MySQL) support ST_IsRing(), but we have an easy fallback
            return $this->isClosed($curve) && $this->isSimple($curve);
        }
    }

    #[Override]
    public function makeValid(Geometry $g): Geometry
    {
        return $this->queryGeometry('ST_MakeValid', $g);
    }

    #[Override]
    public function equals(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('ST_Equals', $a, $b);
    }

    #[Override]
    public function disjoint(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('ST_Disjoint', $a, $b);
    }

    #[Override]
    public function touches(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('ST_Touches', $a, $b);
    }

    #[Override]
    public function crosses(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('ST_Crosses', $a, $b);
    }

    #[Override]
    public function within(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('ST_Within', $a, $b);
    }

    #[Override]
    public function overlaps(Geometry $a, Geometry $b): bool
    {
        return $this->queryBoolean('ST_Overlaps', $a, $b);
    }

    #[Override]
    public function relate(Geometry $a, Geometry $b, string $matrix): bool
    {
        return $this->queryBoolean('ST_Relate', $a, $b, $matrix);
    }

    #[Override]
    public function locateAlong(Geometry $g, float $mValue): Geometry
    {
        return $this->queryGeometry('ST_LocateAlong', $g, $mValue);
    }

    #[Override]
    public function locateBetween(Geometry $g, float $mStart, float $mEnd): Geometry
    {
        return $this->queryGeometry('ST_LocateBetween', $g, $mStart, $mEnd);
    }

    #[Override]
    public function distance(Geometry $a, Geometry $b): float
    {
        return $this->queryFloat('ST_Distance', $a, $b);
    }

    #[Override]
    public function buffer(Geometry $g, float $distance): Geometry
    {
        return $this->queryGeometry('ST_Buffer', $g, $distance);
    }

    #[Override]
    public function convexHull(Geometry $g): Geometry
    {
        return $this->queryGeometry('ST_ConvexHull', $g);
    }

    #[Override]
    public function concaveHull(Geometry $g, float $convexity, bool $allowHoles): Geometry
    {
        return $this->queryGeometry('ST_ConcaveHull', $g, $convexity, $allowHoles);
    }

    #[Override]
    public function symDifference(Geometry $a, Geometry $b): Geometry
    {
        return $this->queryGeometry('ST_SymDifference', $a, $b);
    }

    #[Override]
    public function snapToGrid(Geometry $g, float $size): Geometry
    {
        return $this->queryGeometry('ST_SnapToGrid', $g, $size);
    }

    #[Override]
    public function simplify(Geometry $g, float $tolerance): Geometry
    {
        return $this->queryGeometry('ST_Simplify', $g, $tolerance);
    }

    #[Override]
    public function maxDistance(Geometry $a, Geometry $b): float
    {
        return $this->queryFloat('ST_MaxDistance', $a, $b);
    }

    #[Override]
    public function transform(Geometry $g, int $srid): Geometry
    {
        return $this->queryGeometry('ST_Transform', $g, $srid);
    }

    #[Override]
    public function split(Geometry $g, Geometry $blade): Geometry
    {
        return $this->queryGeometry('ST_Split', $g, $blade);
    }

    #[Override]
    public function lineInterpolatePoint(LineString $lineString, float $fraction): Point
    {
        $result = $this->queryGeometry('ST_LineInterpolatePoint', $lineString, $fraction);
        TypeChecker::check($result, Point::class);

        return $result;
    }

    #[Override]
    public function lineInterpolatePoints(LineString $lineString, float $fraction): MultiPoint
    {
        $result = $this->queryGeometry('ST_LineInterpolatePoints', $lineString, $fraction);

        if ($result instanceof MultiPoint) {
            return $result;
        }

        TypeChecker::check($result, Point::class);

        // POINT EMPTY
        if ($result->isEmpty()) {
            return new MultiPoint($result->coordinateSystem());
        }

        // POINT
        return MultiPoint::of($result);
    }

    /**
     * Executes a SQL query.
     *
     * @param string                         $query      The SQL query to execute.
     * @param list<GeometryParameter|scalar> $parameters The geometry data or scalar values to pass as parameters.
     *
     * @return list<mixed> A numeric result array.
     *
     * @throws GeometryEngineException
     */
    abstract protected function executeQuery(string $query, array $parameters): array;

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
    protected function getGeomFromWkbSyntax(): string
    {
        return 'ST_GeomFromWKB(?, ?)';
    }

    /**
     * Returns the placeholder syntax for the given parameter.
     *
     * This method may be overridden to perform explicit type casts if necessary.
     */
    protected function getParameterPlaceholder(string|float|int|bool $parameter): string
    {
        return '?';
    }

    /**
     * Queries a GIS function returning a Geometry object.
     *
     * @param string          $function      The SQL GIS function to execute.
     * @param Geometry|scalar ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @throws GeometryEngineException
     */
    final protected function queryGeometry(string $function, Geometry|string|float|int|bool ...$parameters): Geometry
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
     * Builds and executes a SQL query for a GIS function.
     *
     * @param string                 $function        The SQL GIS function to execute.
     * @param array<Geometry|scalar> $parameters      The Geometry objects or scalar values to pass as parameters.
     * @param bool                   $returnsGeometry Whether the GIS function returns a Geometry.
     *
     * @return list<mixed> A numeric result array.
     *
     * @throws GeometryEngineException
     */
    private function query(string $function, array $parameters, bool $returnsGeometry): array
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
                    ? $this->getGeomFromWkbSyntax()
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
     * @param string          $function      The SQL GIS function to execute.
     * @param Geometry|scalar ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @throws GeometryEngineException
     */
    private function queryBoolean(string $function, Geometry|string|float|int|bool ...$parameters): bool
    {
        /** @var array{scalar|null} $result */
        $result = $this->query($function, $parameters, false);

        $value = $result[0];

        // SQLite3 returns -1 when calling a boolean GIS function on a NULL result,
        // MariaDB returns -1 when an unsupported operation is performed on a Z/M geometry.
        if ($value === null || $value === -1 || $value === '-1') {
            throw GeometryEngineException::operationYieldedNoResult();
        }

        return (bool) $value;
    }

    /**
     * Queries a GIS function returning a floating point value.
     *
     * @param string          $function      The SQL GIS function to execute.
     * @param Geometry|scalar ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @throws GeometryEngineException
     */
    private function queryFloat(string $function, Geometry|string|float|int|bool ...$parameters): float
    {
        /** @var array{scalar|null} $result */
        $result = $this->query($function, $parameters, false);

        $value = $result[0];

        if ($value === null) {
            throw GeometryEngineException::operationYieldedNoResult();
        }

        return (float) $value;
    }

    /**
     * @return class-string<Proxy\ProxyInterface&Geometry>
     *
     * @throws GeometryEngineException
     */
    private function getProxyClassName(string $geometryType): string
    {
        $proxyClasses = [
            'CIRCULARSTRING' => Proxy\CircularStringProxy::class,
            'COMPOUNDCURVE' => Proxy\CompoundCurveProxy::class,
            'CURVE' => Proxy\CurveProxy::class,
            'CURVEPOLYGON' => Proxy\CurvePolygonProxy::class,
            'GEOMCOLLECTION' => Proxy\GeometryCollectionProxy::class, // MySQL 8 - https://github.com/brick/geo/pull/33
            'GEOMETRY' => Proxy\GeometryProxy::class,
            'GEOMETRYCOLLECTION' => Proxy\GeometryCollectionProxy::class,
            'LINESTRING' => Proxy\LineStringProxy::class,
            'MULTICURVE' => Proxy\MultiCurveProxy::class,
            'MULTILINESTRING' => Proxy\MultiLineStringProxy::class,
            'MULTIPOINT' => Proxy\MultiPointProxy::class,
            'MULTIPOLYGON' => Proxy\MultiPolygonProxy::class,
            'MULTISURFACE' => Proxy\MultiSurfaceProxy::class,
            'POINT' => Proxy\PointProxy::class,
            'POLYGON' => Proxy\PolygonProxy::class,
            'POLYHEDRALSURFACE' => Proxy\PolyhedralSurfaceProxy::class,
            'SURFACE' => Proxy\SurfaceProxy::class,
            'TIN' => Proxy\TinProxy::class,
            'TRIANGLE' => Proxy\TriangleProxy::class,
        ];

        $geometryType = strtoupper($geometryType);
        $geometryType = preg_replace('/^ST_/', '', $geometryType);
        assert($geometryType !== null);
        $geometryType = preg_replace('/ .*/', '', $geometryType);
        assert($geometryType !== null);

        if (! isset($proxyClasses[$geometryType])) {
            throw new GeometryEngineException('Unknown geometry type: ' . $geometryType);
        }

        return $proxyClasses[$geometryType];
    }
}
