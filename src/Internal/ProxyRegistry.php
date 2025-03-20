<?php

declare(strict_types=1);

namespace Brick\Geo\Internal;

use Brick\Geo\Geometry;
use Brick\Geo\Proxy\ProxyInterface;
use WeakMap;

/**
 * This registry is a temporary fix to allow the Geometry class hierarchy to be readonly.
 * We'll get rid of proxies soon by leveraging PHP 8.4's native proxy objects.
 */
final class ProxyRegistry
{
    /**
     * @var WeakMap<ProxyInterface&Geometry, Geometry>|null
     */
    private static ?WeakMap $proxies = null;

    /**
     * @template T of Geometry
     *
     * @param ProxyInterface&T $proxy
     *
     * @return T|null
     */
    public static function getProxiedGeometry(ProxyInterface&Geometry $proxy): ?Geometry
    {
        if (self::$proxies === null) {
            /** @var WeakMap<ProxyInterface&Geometry, Geometry> */
            self::$proxies = new WeakMap();
        }

        /** @var T|null */
        return self::$proxies[$proxy] ?? null;
    }

    public static function hasProxiedGeometry(ProxyInterface&Geometry $proxy) : bool
    {
        return self::$proxies !== null && isset(self::$proxies[$proxy]);
    }

    /**
     * @template T of Geometry
     *
     * @param ProxyInterface&T $proxy
     * @param T $geometry
     */
    public static function setProxiedGeometry(ProxyInterface&Geometry $proxy, Geometry $geometry): void
    {
        if (self::$proxies === null) {
            /** @var WeakMap<ProxyInterface&Geometry, Geometry> */
            self::$proxies = new WeakMap();
        }

        self::$proxies[$proxy] = $geometry;
    }
}
