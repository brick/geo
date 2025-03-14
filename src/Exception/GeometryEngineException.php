<?php

declare(strict_types=1);

namespace Brick\Geo\Exception;

/**
 * Exception thrown by geometry engines.
 *
 * This exception is notably thrown when a specific method is not implemented by a geometry engine.
 */
final class GeometryEngineException extends GeometryException
{
    public static function wrap(\Exception $e) : GeometryEngineException
    {
        return new self('The engine returned an exception: ' . $e->getMessage(), $e);
    }

    public static function unimplementedMethod(string $methodName) : GeometryEngineException
    {
        $message = sprintf('%s() is currently not implemented.', $methodName);

        return new self($message);
    }

    public static function operationYieldedNoResult() : GeometryEngineException
    {
        return new self('This operation yielded no result on the target database.');
    }

    public static function unexpectedReturnType(
        string $expectedClassName,
        string $actualClassName,
    ) : GeometryEngineException {
        return new self(sprintf(
            'The geometry engine returned an unexpected type: expected %s, got %s.',
            $expectedClassName,
            $actualClassName,
        ));
    }
}
