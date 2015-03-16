<?php

namespace Brick\Geo\Proxy;

/**
 * Interface implemented by geometry proxies.
 */
interface ProxyInterface
{
    /**
     * Returns whether the underlying Geometry is loaded.
     *
     * @return boolean
     */
    public function isLoaded();

    /**
     * Loads and returns the underlying Geometry.
     *
     * @return \Brick\Geo\Geometry
     */
    public function getGeometry();
}
