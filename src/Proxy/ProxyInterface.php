<?php

namespace Brick\Geo\Proxy;

use Brick\Geo\Geometry;

/**
 * Interface implemented by geometry proxies.
 */
interface ProxyInterface
{
    /**
     * Returns whether the underlying Geometry is loaded.
     */
    public function isLoaded() : bool;

    /**
     * Loads and returns the underlying Geometry.
     */
    public function getGeometry() : Geometry;

    /**
     * Returns whether the underlying data is WKB (true) or WKT (false).
     */
    public function isProxyBinary() : bool;
}
