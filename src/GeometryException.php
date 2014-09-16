<?php

namespace Brick\Geo;

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
        return new self('This platform has an unsupported endianness');
    }

    /**
     * @param string $methodName
     *
     * @return GeometryException
     */
    public static function unimplementedMethod($methodName)
    {
        $message = sprintf('%s() is currently not implemented', $methodName);

        return new self($message);
    }

    /**
     * @return GeometryException
     */
    public static function invalidWkt()
    {
        return new self('Invalid WKT');
    }

    /**
     * @return GeometryException
     */
    public static function invalidWkb()
    {
        return new self('Invalid WKB');
    }

    /**
     * @param string $wkbType
     *
     * @return GeometryException
     */
    public static function unsupportedWkbType($wkbType)
    {
        $message = sprintf('Unsupported WKB type: %s', $wkbType);

        return new self($message);
    }

    /**
     * @param Geometry $geometry
     *
     * @return GeometryException
     */
    public static function unsupportedGeometryType(Geometry $geometry)
    {
        $message = sprintf('Unsupported geometry type: ' . $geometry->geometryType());

        return new self($message);
    }

    /**
     * @param string $expectedClass
     * @param string $actualClass
     *
     * @return GeometryException
     */
    public static function unexpectedGeometryType($expectedClass, $actualClass)
    {
        $message = sprintf('Unexpected geometry type: %s expected, %s actual', $expectedClass, $actualClass);

        return new self($message);
    }

    /**
     * @return GeometryException
     */
    public static function noServiceInjected()
    {
        return new self('No GeometryService has been injected to support this feature');
    }
}
