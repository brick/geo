<?php

declare(strict_types=1);

namespace Brick\Geo\Io\Internal;

use InvalidArgumentException;
use stdClass;

/**
 * @internal This class is not part of the public API and can change at any time.
 */
final class Cloner
{
    /**
     * @psalm-suppress RawObjectIteration
     * @psalm-suppress MixedAssignment
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     *
     * @template T
     *
     * @param T $variable
     *
     * @return T
     *
     * @throws InvalidArgumentException
     */
    public static function clone(mixed $variable): mixed
    {
        if (is_object($variable)) {
            if (! $variable instanceof stdClass) {
                throw new InvalidArgumentException('This function can only clone stdClass objects.');
            }

            $variable = clone $variable;

            foreach ($variable as $prop => $value) {
                $variable->{$prop} = self::clone($value);
            }
        }

        if (is_array($variable)) {
            foreach ($variable as $key => $value) {
                $variable[$key] = self::clone($value);
            }
        }

        return $variable;
    }
}
