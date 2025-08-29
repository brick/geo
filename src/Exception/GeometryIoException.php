<?php

declare(strict_types=1);

namespace Brick\Geo\Exception;

use JsonException;

use function sprintf;

/**
 * Exception thrown when an error occurs reading or writing WKT/WKB representations.
 */
final class GeometryIoException extends GeometryException
{
    public static function invalidWkb(string $message): GeometryIoException
    {
        return new self('Invalid WKB: ' . $message);
    }

    public static function invalidWkt(): GeometryIoException
    {
        return new self('Invalid WKT.');
    }

    public static function invalidEwkt(): GeometryIoException
    {
        return new self('Invalid EWKT.');
    }

    public static function invalidGeoJson(string $context, ?JsonException $e = null): GeometryIoException
    {
        $message = sprintf('Invalid GeoJSON: %s', $context);

        return new self($message, $e);
    }

    public static function unsupportedWkbType(int $wkbType): GeometryIoException
    {
        $message = sprintf('Unsupported WKB type: %d.', $wkbType);

        return new self($message);
    }

    public static function unsupportedGeoJsonType(string $geoJsonType): GeometryIoException
    {
        $message = sprintf('Unsupported GeoJSON type: %s.', $geoJsonType);

        return new self($message);
    }

    public static function unsupportedGeoJsonTypeWrongCase(string $wrongCase, string $correctCase): GeometryIoException
    {
        $message = sprintf(
            'Unsupported GeoJSON type: %s. The correct case is %s. ' .
            'You can allow incorrect cases by setting the $lenient flag to true.',
            $wrongCase,
            $correctCase,
        );

        return new self($message);
    }

    public static function unsupportedGeometryType(string $geometryType): GeometryIoException
    {
        $message = sprintf('Unsupported geometry type: %s.', $geometryType);

        return new self($message);
    }

    public static function unsupportedEndianness(): GeometryIoException
    {
        return new self('This platform has an unsupported endianness.');
    }
}
