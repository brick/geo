<?php

namespace Brick\Geo\Engine;

use Brick\Geo\Exception\GeometryEngineException;

/**
 * This class holds the GeometryEngine implementation to use for calculations.
 */
final class GeometryEngineRegistry
{
    /**
     * @var GeometryEngine|null
     */
    private static $engine;

    /**
     * Returns whether a geometry engine is set.
     *
     * @return bool
     */
    public static function has()
    {
        return self::$engine !== null;
    }

    /**
     * Sets the GeometryEngine to use for calculations.
     *
     * @param GeometryEngine $engine
     *
     * @return void
     */
    public static function set(GeometryEngine $engine)
    {
        self::$engine = $engine;
    }

    /**
     * Returns the GeometryEngine to use for calculations.
     *
     * @return GeometryEngine
     *
     * @throws GeometryEngineException
     */
    public static function get()
    {
        if (self::$engine === null) {
            throw GeometryEngineException::noEngineSet();
        }

        return self::$engine;
    }
}
