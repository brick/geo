<?php

declare(strict_types=1);

namespace Brick\Geo\Engine\Database\Driver\Internal;

use Brick\Geo\Exception\GeometryEngineException;

final class TypeConverter
{
    /**
     * Safely converts a string to integer, with no value loss.
     *
     * @throws GeometryEngineException
     */
    public static function convertStringToInt(string $value) : int
    {
        $intValue = (int) $value;

        if ($value === (string) $intValue) {
            return $intValue;
        }

        if ($value === '-0' || preg_match('/^-?[0-9]+$/', $value) !== 1) {
            throw new GeometryEngineException(sprintf(
                'The database returned an unexpected type: expected integer string, got %s.',
                var_export($value, true),
            ));
        }

        throw new GeometryEngineException('The database return an out of range integer: ' . $value);
    }
}
