<?php

namespace Brick\Geo\Exception;

/**
 * Default exception thrown by Geometry objects.
 */
class GeometryException extends \Exception
{
    /**
     * @return GeometryException
     */
    public static function unsupportedPlatform()
    {
        return new self('This platform has an unsupported endianness.');
    }

    /**
     * @param string $geometryType
     *
     * @return static
     */
    public static function unsupportedGeometryType($geometryType)
    {
        $message = sprintf('Unsupported geometry type: %s.', $geometryType);

        return new static($message);
    }
}
