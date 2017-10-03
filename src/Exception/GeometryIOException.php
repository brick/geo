<?php

namespace Brick\Geo\Exception;

/**
 * Exception thrown when an error occurs reading or writing WKT/WKB representations.
 */
class GeometryIOException extends GeometryException
{
    /**
     * @param string $message
     *
     * @return GeometryIOException
     */
    public static function invalidWKB(string $message) : GeometryIOException
    {
        return new self('Invalid WKB: ' . $message);
    }

    /**
     * @return GeometryIOException
     */
    public static function invalidWKT() : GeometryIOException
    {
        return new self('Invalid WKT.');
    }

    /**
     * @return GeometryIOException
     */
    public static function invalidEWKT() : GeometryIOException
    {
        return new self('Invalid EWKT.');
    }

    /**
     * @param string $wkbType
     *
     * @return GeometryIOException
     */
    public static function unsupportedWKBType(string $wkbType) : GeometryIOException
    {
        $message = sprintf('Unsupported WKB type: %s.', $wkbType);

        return new self($message);
    }

    /**
     * @param string $geometryType
     *
     * @return GeometryIOException
     */
    public static function unsupportedGeometryType(string $geometryType) : GeometryIOException
    {
        $message = sprintf('Unsupported geometry type: %s.', $geometryType);

        return new static($message);
    }

    /**
     * @return GeometryIOException
     */
    public static function unsupportedEndianness() : GeometryIOException
    {
        return new self('This platform has an unsupported endianness.');
    }
}
