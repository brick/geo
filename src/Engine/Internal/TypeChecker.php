<?php

declare(strict_types=1);

namespace Brick\Geo\Engine\Internal;

use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Geometry;

/**
 * Type checker for engine return values.
 */
final readonly class TypeChecker
{
    /**
     * Checks that the given geometry is an instance of the expected class.
     *
     * @template T of Geometry
     * @psalm-assert T $geometry
     *
     * @param class-string<T> $className
     *
     * @throws GeometryEngineException
     */
    public static function check(Geometry $geometry, string $className) : void
    {
        if (! $geometry instanceof $className) {
            throw GeometryEngineException::unexpectedReturnType($className, get_class($geometry));
        }
    }
}
