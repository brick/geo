<?php

declare(strict_types=1);

namespace Brick\Geo\Exception;

use JsonException;

/**
 * Exception thrown when an error occurs reading or writing WKT/WKB representations.
 */
class GeometryIOException extends GeometryException
{
    public static function invalidWKB(string $message) : GeometryIOException
    {
        return new self('Invalid WKB: ' . $message);
    }

    public static function invalidWKT() : GeometryIOException
    {
        return new self('Invalid WKT.');
    }

    public static function invalidEWKT() : GeometryIOException
    {
        return new self('Invalid EWKT.');
    }

    public static function invalidGeoJSON(string $context, ?JsonException $e = null) : GeometryIOException
    {
        $message = sprintf('Invalid GeoJSON: %s', $context);

        return new self($message);
    }

    public static function unsupportedWKBType(int $wkbType) : GeometryIOException
    {
        $message = sprintf('Unsupported WKB type: %d.', $wkbType);

        return new self($message);
    }

    public static function unsupportedGeoJSONType(string $geojsonType) : GeometryIOException
    {
        $message = sprintf('Unsupported GeoJSON type: %s.', $geojsonType);

        return new self($message);
    }

    public static function unsupportedGeometryType(string $geometryType) : GeometryIOException
    {
        $message = sprintf('Unsupported geometry type: %s.', $geometryType);

        return new self($message);
    }

    public static function unsupportedEndianness() : GeometryIOException
    {
        return new self('This platform has an unsupported endianness.');
    }
}
