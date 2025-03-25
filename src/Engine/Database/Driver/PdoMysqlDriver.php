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
 * Database driver using a pdo_mysql connection.
 */
final class PdoMysqlDriver extends AbstractPdoDriver
{
    #[Override]
    public function convertQuery(string|BinaryValue|ScalarValue ...$query) : array
    {
        $queryString = '';
        $queryParams = [];

        foreach ($query as $queryPart) {
            if ($queryPart instanceof BinaryValue) {
                $queryString .= 'BINARY ?';
                $queryParams[] = [$queryPart->value, PDO::PARAM_LOB];
            } elseif ($queryPart instanceof ScalarValue) {
                $queryString .= '?';

                if (is_int($queryPart->value)) {
                    $queryParams[] = [$queryPart->value, PDO::PARAM_INT];
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
        if (is_string($value)) {
            return $value;
        }

        throw GeometryEngineException::unexpectedDatabaseReturnType('string', $value);
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
        return match ($value) {
            0 => false,
            1 => true,
            default => throw GeometryEngineException::unexpectedDatabaseReturnType('0 or 1', $value),
        };
    }
}
