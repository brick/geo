<?php

declare(strict_types=1);

namespace Brick\Geo\Engine\Database\Driver;

use Brick\Geo\Engine\Database\Driver\Internal\AbstractPdoDriver;
use Brick\Geo\Engine\Database\Query\BinaryValue;
use Brick\Geo\Engine\Database\Query\ScalarValue;
use Brick\Geo\Exception\GeometryEngineException;
use Override;
use PDO;

/**
 * Database driver using a pdo_pgsql connection.
 */
final class PdoPgsqlDriver extends AbstractPdoDriver
{
    #[Override]
    public function convertQuery(string|BinaryValue|ScalarValue ...$query) : array
    {
        $queryString = '';
        $queryParams = [];

        foreach ($query as $queryPart) {
            if ($queryPart instanceof BinaryValue) {
                $queryString .= '?';
                $queryParams[] = [$queryPart->value, PDO::PARAM_LOB];
            } elseif ($queryPart instanceof ScalarValue) {
                $queryString .= '?';

                if (is_int($queryPart->value)) {
                    $queryString .= '::int'; // PARAM_INT seems to have no effect on pdo_pgsql
                    $queryParams[] = [$queryPart->value, PDO::PARAM_INT];
                } elseif (is_float($queryPart->value)) {
                    $queryString .= '::float';
                    $queryParams[] = [$queryPart->value, PDO::PARAM_STR];
                } elseif (is_bool($queryPart->value)) {
                    $queryParams[] = [$queryPart->value, PDO::PARAM_BOOL];
                } else {
                    $queryParams[] = [$queryPart->value, PDO::PARAM_STR];
                }
            } else {
                $queryString .= $queryPart;
            }
        }

        return [$queryString, $queryParams];
    }

    #[Override]
    public function convertBinaryResult(mixed $value) : string
    {
        if (is_resource($value)) {
            $value = stream_get_contents($value);

            if ($value === false) {
                throw new GeometryEngineException('Failed to read stream contents.');
            }

            return $value;
        }

        throw GeometryEngineException::unexpectedDatabaseReturnType('resource', $value);
    }

    #[Override]
    public function convertStringResult(mixed $value) : string
    {
        if (is_string($value)) {
            return $value;
        }

        throw GeometryEngineException::unexpectedDatabaseReturnType('string', $value);
    }

    #[Override]
    public function convertIntResult(mixed $value) : int
    {
        if (is_int($value)) {
            return $value;
        }

        throw GeometryEngineException::unexpectedDatabaseReturnType('int', $value);
    }

    #[Override]
    public function convertFloatResult(mixed $value) : float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        throw GeometryEngineException::unexpectedDatabaseReturnType('number or numeric string', $value);
    }

    #[Override]
    public function convertBoolResult(mixed $value) : bool
    {
        if (is_bool($value)) {
            return $value;
        }

        throw GeometryEngineException::unexpectedDatabaseReturnType('bool', $value);
    }
}
