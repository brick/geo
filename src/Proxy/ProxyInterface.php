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
     *
     * @return bool
     */
    public function isLoaded();

    /**
     * Loads and returns the underlying Geometry.
     *
     * @return Geometry
     */
    public function getGeometry();
}
