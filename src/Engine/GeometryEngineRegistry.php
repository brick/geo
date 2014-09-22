<?php

namespace Brick\Geo\Engine;

use Brick\Geo\Exception\GeometryException;

/**
 * This class holds the GeometryEngine implementation to use for calculations.
 */
class GeometryEngineRegistry
{
    /**
     * @var GeometryEngine|null
     */
    private static $engine;

    /**
     * Sets the GeometryEngine to use for calculations.
     *
     * @param GeometryEngine $engine
     *
     * @return void
     */
    final public static function set(GeometryEngine $engine)
    {
        self::$engine = $engine;
    }

    /**
     * Returns the GeometryEngine to use for calculations.
     *
     * @return GeometryEngine
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    final public static function get()
    {
        if (self::$engine === null) {
            throw GeometryException::noEngineSet();
        }

        return self::$engine;
    }
}
