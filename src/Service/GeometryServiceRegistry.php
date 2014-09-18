<?php

namespace Brick\Geo\Service;

use Brick\Geo\GeometryException;

/**
 * This class holds the GeometryService implementation to use for calculations.
 */
class GeometryServiceRegistry
{
    /**
     * @var GeometryService|null
     */
    private static $service;

    /**
     * Sets the GeometryService to use for calculations.
     *
     * @param GeometryService $service
     *
     * @return void
     */
    final public static function set(GeometryService $service)
    {
        self::$service = $service;
    }

    /**
     * Returns the GeometryService to use for calculations.
     *
     * @return GeometryService
     *
     * @throws GeometryException
     */
    final public static function get()
    {
        if (self::$service === null) {
            throw GeometryException::noServiceSet();
        }

        return self::$service;
    }
}
