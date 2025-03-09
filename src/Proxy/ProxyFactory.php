<?php

declare(strict_types=1);

namespace Brick\Geo\Proxy;

use Brick\Geo\Exception\UnexpectedGeometryException;
use Brick\Geo\Geometry;
use Brick\Geo\Io\EwkbReader;
use Brick\Geo\Io\EwktReader;
use Brick\Geo\Io\WkbReader;
use Brick\Geo\Io\WktReader;
use Closure;
use ReflectionClass;

/**
 * Creates proxies to lazily load geometries.
 */
final readonly class ProxyFactory
{
    /**
     * Creates a proxy to lazily load a Geometry from WKB data.
     *
     * @template T of Geometry
     *
     * @param class-string<T> $geometryClass The non-abstract Geometry class to create a proxy for.
     * @param string          $wkb           The WKB data that represents an instance of the given class.
     * @param int             $srid          The optional SRID of the geometry.
     *
     * @return T
     */
    public static function createWkbProxy(string $geometryClass, string $wkb, int $srid = 0) : Geometry
    {
        return self::createProxy($geometryClass, fn() => new WkbReader()->read($wkb, $srid));
    }

    /**
     * Creates a proxy to lazily load a Geometry from WKT data.
     *
     * @template T of Geometry
     *
     * @param class-string<T> $geometryClass The non-abstract Geometry class to create a proxy for.
     * @param string          $wkt           The WKT data that represents an instance of the given class.
     * @param int             $srid          The optional SRID of the geometry.
     *
     * @return T
     */
    public static function createWktProxy(string $geometryClass, string $wkt, int $srid = 0) : Geometry
    {
        return self::createProxy($geometryClass, fn() => new WktReader()->read($wkt, $srid));
    }

    /**
     * Creates a proxy to lazily load a Geometry from EWKB data.
     *
     * @template T of Geometry
     *
     * @param class-string<T> $geometryClass The non-abstract Geometry class to create a proxy for.
     * @param string          $ewkb          The EWKB data that represents an instance of the given class.
     *
     * @return T
     */
    public static function createEwkbProxy(string $geometryClass, string $ewkb) : Geometry
    {
        return self::createProxy($geometryClass, fn() => new EwkbReader()->read($ewkb));
    }

    /**
     * Creates a proxy to lazily load a Geometry from EWKT data.
     *
     * @template T of Geometry
     *
     * @param class-string<T> $geometryClass The non-abstract Geometry class to create a proxy for.
     * @param string          $ewkt          The EWKT data that represents an instance of the given class.
     *
     * @return T
     */
    public static function createEwktProxy(string $geometryClass, string $ewkt) : Geometry
    {
        return self::createProxy($geometryClass, fn() => new EwktReader()->read($ewkt));
    }

    /**
     * Creates a proxy to lazily load a Geometry from a closure.
     *
     * @template T of Geometry
     *
     * @param class-string<T>     $geometryClass The non-abstract Geometry class to create a proxy for.
     * @param Closure(): Geometry $readGeometry  A closure that creates a Geometry of the given class.
     *
     * @return T
     */
    public static function createProxy(string $geometryClass, Closure $readGeometry) : Geometry
    {
        return new ReflectionClass($geometryClass)->newLazyProxy(
            function() use ($geometryClass, $readGeometry) {
                $geometry = $readGeometry();

                if ($geometry instanceof $geometryClass) {
                    return $geometry;
                }

                throw UnexpectedGeometryException::unexpectedGeometryType($geometryClass, $geometry);
            },
        );
    }
}
