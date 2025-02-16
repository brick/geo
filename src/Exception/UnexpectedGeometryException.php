<?php

declare(strict_types=1);

namespace Brick\Geo\Exception;

/**
 * Exception thrown when a geometry is found different from the expected type.
 */
final class UnexpectedGeometryException extends GeometryException
{
    public static function unexpectedGeometryType(string $expectedClass, mixed $value) : UnexpectedGeometryException
    {
        $value = get_debug_type($value);
        $message = sprintf('Unexpected geometry type: expected %s, got %s.', $expectedClass, $value);

        return new self($message);
    }
}
