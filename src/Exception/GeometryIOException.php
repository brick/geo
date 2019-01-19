<?php

declare(strict_types=1);

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
     * @return GeometryIOException
     */
    public static function invalidGeoJSON() : GeometryIOException
    {
        return new self('Invalid GeoJson.');
    }

    /**
     * @param int $wkbType
     *
     * @return GeometryIOException
     */
    public static function unsupportedWKBType(int $wkbType) : GeometryIOException
    {
        $message = sprintf('Unsupported WKB type: %d.', $wkbType);

        return new self($message);
    }

    /**
     * @param string $geojsonType
     *
     * @return GeometryIOException
     */
    public static function unsupportedGeoJSONType(string $geojsonType) : GeometryIOException
    {
        $message = sprintf('Unsupported GeoJSON type: %s.', $geojsonType);

        return new static($message);
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
