<?php

declare(strict_types=1);

namespace Brick\Geo\Engine\Internal;

use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Geometry;

use function get_class;

/**
 * Type checker for engine return values.
 */
final class TypeChecker
{
    /**
     * Checks that the given geometry is an instance of the expected class.
     *
     * @template T of Geometry
     *
     * @param class-string<T> $className
     *
     * @throws GeometryEngineException
     *
     * @psalm-assert T $geometry
     */
    public static function check(Geometry $geometry, string $className): void
    {
        if (! $geometry instanceof $className) {
            throw GeometryEngineException::unexpectedReturnType($className, get_class($geometry));
        }
    }
}
