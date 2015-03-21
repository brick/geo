<?php

namespace Brick\Geo\Exception;

/**
 * Exception thrown when parsing invalid WKT or WKB representations.
 */
class GeometryParseException extends GeometryException
{
    /**
     * @param string $message
     *
     * @return GeometryParseException
     */
    public static function invalidWKB($message)
    {
        return new self('Invalid WKB: ' . $message);
    }

    /**
     * @return GeometryParseException
     */
    public static function invalidWKT()
    {
        return new self('Invalid WKT.');
    }

    /**
     * @return GeometryParseException
     */
    public static function invalidEWKT()
    {
        return new self('Invalid EWKT.');
    }

    /**
     * @param string $wkbType
     *
     * @return GeometryParseException
     */
    public static function unsupportedWKBType($wkbType)
    {
        $message = sprintf('Unsupported WKB type: %s.', $wkbType);

        return new self($message);
    }
}
