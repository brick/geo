<?php

namespace Brick\Geo\Exception;

/**
 * Exception thrown when a geometry is found different from the expected type.
 */
class UnexpectedGeometryException extends GeometryException
{
    /**
     * @param string $expectedClass
     * @param mixed  $value
     *
     * @return GeometryException
     */
    public static function unexpectedGeometryType($expectedClass, $value)
    {
        $value = is_object($value) ? get_class($value) : gettype($value);
        $message = sprintf('Unexpected geometry type: expected %s, got %s.', $expectedClass, $value);

        return new self($message);
    }
}
